<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Form\IoExportType;
use App\Form\IoImportType;
use App\Security\CurrentContext;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\TerminExcelImportService;
use App\Service\Calendar\TerminExportRendererService;
use App\Service\Calendar\TerminExportQueryFactory;
use App\Service\Presentation\TemplatePathResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DfxImportExportController extends AbstractController
{
    public function __construct(
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly TerminExportQueryFactory $terminExportQueryFactory,
        private readonly TerminExcelImportService $terminExcelImportService,
        private readonly TerminExportRendererService $terminExportRendererService,
        private readonly CurrentContext $currentContext,
    ) {
    }

    #[Template("Admin/io.html.twig")]
    #[Route(path: '/io/', name: 'io', methods: ['GET'])]
    public function index(): Response
    {
        $konf = $this->requireKonf();
        if ($konf instanceof Response) {
            return $konf;
        }

        $formImport = $this->createImportForm();
        $formExport = $this->createExportForm($konf);
        $tpl = $this->templatePathResolver->resolve('Admin', 'io.html.twig', $konf);

        return $this->render($tpl, [
            'formImport' => $formImport,
            'formExport' => $formExport,
        ]);
    }

    private function createImportForm(): FormInterface
    {
        return $this->createForm(IoImportType::class, null, [
            'action' => $this->generateUrl('termine_import'),
            'attr' => ['name' => 'import', 'id' => 'import'],
        ]);
    }

    private function createExportForm(DfxKonf $konf): FormInterface
    {
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);

        return $this->createForm(IoExportType::class, null, [
            'konf' => $konf,
            'calendar_scope' => $calendarScope,
            'action' => $this->generateUrl('termine_export'),
            'attr' => ['name' => 'export', 'id' => 'export'],
        ]);
    }


    #[Template("Admin/import.html.twig")]
    #[Route(path: '/io/import', name: 'termine_import', methods: ['GET', 'POST'])]
    public function import(Request $request): array|Response
    {
        $konf = $this->requireKonf();
        if ($konf instanceof Response) {
            return $konf;
        }

        $user = $this->currentContext->getUser();
        $form = $this->createImportForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('xlsfile')->getData();
            $echo = $file !== null
                ? $this->terminExcelImportService->import($file, $user, $konf)
                : 'Keine Upload-Datei gefunden';
        } else {
            $echo = $form->getErrors(true, false);
        }

        return [
            'msg' => $echo,
        ];
    }

    #[Template("Admin/export.html.twig")]
    #[Route(path: '/io/export', name: 'termine_export', methods: ['POST'])]
    public function export(Request $request): Response|array
    {
        $konf = $this->requireKonf();
        if ($konf instanceof Response) {
            return $konf;
        }

        $form = $this->createExportForm($konf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exportConfig = $this->terminExportQueryFactory->build($konf, $form->getData());
            $strHeader = $exportConfig['header'];
            $query_order = clone $exportConfig['query'];
            $query_order
                ->orderBy('t.datumVon, t.zeit');
            $sql = $query_order->getQuery();
            $entities = $sql->getResult();
            return $this->terminExportRendererService->render(
                $konf,
                $entities,
                (string) $form->get('exportTyp')->getData(),
                (bool) $form->get('stripTags')->getData(),
                $strHeader,
            );
        }

        return [
            'msg' => 'Keine Filterdaten empfangen',
        ];
    }

    private function requireKonf(): DfxKonf|Response
    {
        $konf = $this->currentContext->getUser()->getDatefix();
        if ($konf instanceof DfxKonf) {
            return $konf;
        }

        return new Response('Fehler: Es ist kein Datefix-Account mit diesem User verknüpft');
    }
}
