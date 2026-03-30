<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Form\NewsFrontendFilterType;
use App\Service\Analytics\UsageTrackingService;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\NewsFrontendFilterData;
use App\Service\Calendar\NewsFrontendQueryFactory;
use App\Service\Messaging\MailDeliveryService;
use App\Service\Frontend\CodeChallengeService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\PdfResponseService;
use App\Service\Presentation\TemplatePathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Attribute\Route;
class DfxNewsFrontendController extends AbstractController
{
    public function __construct(
        private readonly CodeChallengeService $codeChallengeService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly MailDeliveryService $mailDeliveryService,
        private readonly PdfResponseService $pdfResponseService,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly NewsFrontendQueryFactory $newsFrontendQueryFactory,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly UsageTrackingService $usageTrackingService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/js/news/{kid}', name: 'news', methods: ['GET', 'POST'])]
    public function index(int $kid, Request $request, PaginatorInterface $paginator): Response
    {
        $konf = $this->loadKonf($kid);
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $form = $this->createFilterForm($konf, $calendarScope->ids());
        $form->handleRequest($request);
        $data = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];
        $filterData = new NewsFrontendFilterData(
            rubrik: ($data['rubrik'] ?? null) ?: null,
            zielgruppe: ($data['zielgruppe'] ?? null) ?: null,
            filter1: (bool) ($data['filter1'] ?? false),
            filter2: (bool) ($data['filter2'] ?? false),
            filter3: (bool) ($data['filter3'] ?? false),
            filter4: (bool) ($data['filter4'] ?? false),
            filter5: (bool) ($data['filter5'] ?? false),
            suche: ($data['suche'] ?? null) ?: null,
            datumVon: $data['datum_von'] ?? null,
            datumBis: $data['datum_bis'] ?? null,
        );
        $queryConfig = $this->newsFrontendQueryFactory->build($konf, $filterData);

        $filterForm = $form->createView();

        /** @var SlidingPagination $news */
        $news = $paginator->paginate($queryConfig['query'], $request->query->getInt('nfxp', 1), $konf->getItemsListe());
        $news->setParam('filter', $this->buildPaginationFilter($queryConfig['filter']));
        $this->usageTrackingService->track($konf);
        $tpl = $this->templatePathResolver->resolveNewsList($konf);
        $tplform = $this->templatePathResolver->resolveFormTemplatePrefix('News', $kid);
        return $this->htmlResponseService->render($tpl, [
            'news' => $news,
            'konf' => $konf,
            'nav' => $konf->getNavListe(),
            'headline' => $queryConfig['header'],
            'tplform' => $tplform,
            'filter_form' => $filterForm,
        ]);
    }

    #[Route(path: '/js/news/widget/{kid}', name: 'kalender_widget', methods: ['GET'])]
    public function widget(int $kid, Request $request): Response
    {
        $konf = $this->loadKonf($kid);

        $form = $this->createFilterForm($konf, $this->calendarScopeResolver->resolveReadScope($konf)->ids());
        $form->handleRequest($request);
        $filterForm = $form->createView();
        $tpl = $this->templatePathResolver->resolve('DfxNfxWidgets','widget_news.html.twig', $konf);
        $options = ['konf' => $konf, 'filter_form' => $filterForm, 'widget' => 1];
        return $this->htmlResponseService->render($tpl, $options);
    }

    private function createFilterForm(DfxKonf $konf, ?array $calendarIds = null): FormInterface
    {
        $calendarIds ??= $this->calendarScopeResolver->resolveReadScope($konf)->ids();
        return $this->createForm(NewsFrontendFilterType::class, ['kids' => $calendarIds], [
            'konf' => $konf,
            'zielgruppe_enabled' => (bool) $this->getParameter('zielgruppe'),
            'action' => $this->generateUrl('news', ['kid' => $konf->getId()]),
        ]);
    }

    #[Route(path: '/js/news/{kid}/detail/{id}', name: 'artikel_detail', methods: ['GET'])]
    public function artikelShow(int $kid, int $id, Request $request): Response
    {
        $konf = $this->loadKonf($kid);
        $artikel = $this->loadArtikel($id);
        if ($artikel->getDatefix()->getId() != $kid && $konf->getIsMeta() != 1 && $konf->getIsGroup() != 1) {
            throw $this->createNotFoundException('Artikel gehört nicht zu diesem Kalender.');
        }

        if ($request->query->get('cb') == 'all') {
            $form = $this->createFilterForm($konf);
            $filterForm = $form->createView();
            $cb = 'all';
        } else {
            $cb = null;
            $filterForm = null;
        }

        $imgPath = $this->getParameter('kernel.project_dir') . "/web/images/dfx/" . $artikel->getDatefix()->getId() . '/';
        if ($artikel->getImg() && !file_exists($imgPath . $artikel->getImg())) {
            $artikel->setImg(null);
        }

        $tplDetail = 'detail';
        $tplOwnDetail = 'detail';
        $tpl = $this->templatePathResolver->resolveNewsDetail($konf, $tplDetail, $tplOwnDetail);
        $tplform = $this->templatePathResolver->resolveFormTemplatePrefix('News', $kid);
        $custom = $this->templatePathResolver->resolveCustomBasePrefix('News', $kid, 'base_detail');

        $options = ['konf' => $konf, 'artikel' => $artikel,  'nav' => $konf->getNavDetail(), 'cb' => $cb, 'tplform' => $tplform, 'custom' => $custom];
        if ($filterForm != null) {
            $options['filter_form'] = $filterForm;
        }

        $this->usageTrackingService->track($konf, null, $artikel);
        return $this->htmlResponseService->render($tpl, $options);
    }

    #[Route(path: '/js/news/pdf/{id}', name: 'artikel_fe_pdf', methods: ['GET'])]
    public function pdfAction(int $id): Response
    {
        $artikel = $this->loadArtikel($id);
        $konf = $artikel->getDatefix();
        $tpl = $this->templatePathResolver->resolve('News', 'detail_pdf.html.twig', $konf);
        $html = $this->render($tpl, ['artikel' => $artikel, 'konf' => $konf])->getContent();

        return $this->pdfResponseService->render($html);
    }

    #[Route(path: '/js/news/print/{id}', name: 'artikel_print', methods: ['GET'])]
    public function print(int $id): Response
    {
        $artikel = $this->loadArtikel($id);
        $konf = $artikel->getDatefix();
        $tpl = $this->templatePathResolver->resolve('News', 'detail_print.html.twig', $konf);
        return $this->htmlResponseService->render($tpl, ['artikel' => $artikel, 'konf' => $konf]);
    }

    #[Template("News/form_mail.html.twig")]
    #[Route(path: '/js/news/{kid}/mail/{id}', name: 'artikel_mail', methods: ['GET', 'POST'])]
    public function mail(int $kid, int $id, Request $request): Response
    {
        $artikel = $this->loadArtikel($id);
        $konf = $artikel->getDatefix();
        $arCode = $this->codeChallengeService->create();
        $form = $this->createMailForm($kid, $id, $arCode['key']);

        $form->handleRequest($request);
        $error = '';
        if ($form->isSubmitted() && $form->isValid()) {
            if (true === $this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())) {
                $sendEmail = $form->get('sendEmail')->getData();
                $sendVorname = $form->get('sendVorname')->getData();
                $sendNachname = $form->get('sendNachname')->getData();
                $empfEmail = $form->get('empfEmail')->getData();
                $subject = 'Hinweis auf Artikel von ' . $sendVorname . ' ' . $sendNachname;
                $replyTo = $sendEmail;
                $to = $empfEmail;
                $template = 'emailsenn.html.twig';
                $options = ['artikel' => $artikel, 'get' => $_GET];
                $this->mailDeliveryService->sendTemplate($template, $kid, $options, $to, $subject, $replyTo);

                return $this->htmlResponseService->render('News/sent_mail.html.twig', ['artikel' => $artikel, 'konf' => $konf, 'empfEmail' => $empfEmail]);
            } else {
                $error .= 'Fehler cC';
            }
        }

        $error .= $form->getErrors(true);
        return $this->htmlResponseService->render('News/form_mail.html.twig', [
            'artikel' => $artikel,
            'konf' => $konf,
            'form' => $form->createView(),
            'code' => $arCode,
            'error' => $error,
            'filter_form' => null,
        ]);
    }

    #[Route(path: '/js/news/check/{code}/{key}', name: 'checkCode', methods: ['GET'])]
    public function checkCoderand(string $code, string $key): JsonResponse
    {
        return $this->corsJsonResponse($this->codeChallengeService->isValid($code, $key) ? 'ok' : 'error');
    }

    private function createMailForm(int $kid, int $id, string $captchaKey): FormInterface
    {
        return $this->createFormBuilder(['key' => $captchaKey], ['method' => 'GET', 'attr' => ['name' => 'mailform', 'id' => 'mailform']])
            ->setAction($this->generateUrl('artikel_mail', ['kid' => $kid, 'id' => $id]))
            ->add('sendEmail', EmailType::class, ['label' => 'Absender E-Mail', 'required' => true, 'attr' => ['placeholder' => 'Ihre E-Mail-Adresse']])
            ->add('sendVorname', TextType::class, ['label' => 'Absender Vorname', 'required' => true, 'attr' => ['placeholder' => 'Ihr Vorname']])
            ->add('sendNachname', TextType::class, ['label' => 'Absender Nachname ', 'required' => true, 'attr' => ['placeholder' => 'Ihr Nachname']])
            ->add('empfEmail', EmailType::class, ['label' => 'Empfänger E-Mail', 'required' => true, 'attr' => ['placeholder' => 'E-Mail-Adresse des Empfängers']])
            ->add('kommentar', TextareaType::class, ['label' => 'Nachricht an Empfänger', 'required' => false, 'attr' => ['rows' => '4']])
            ->add('cCode', TextType::class, ['label' => false, 'required' => true, 'attr' => ['placeholder' => '4-stellige Codezahl']])
            ->add('key', HiddenType::class)
            ->add('datenschutz', CheckboxType::class, ['label' => false, 'required' => true, 'attr' => ['noFormControl' => true]])
            ->add('submit', SubmitType::class, ['label' => 'Artikel als Mail versenden', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();
    }

    private function buildPaginationFilter(array $filter): string
    {
        $parts = [];
        foreach ($filter as $key => $val) {
            if ($key === 'nfxp') {
                $parts[] = '&' . $key . '=' . $val;
                continue;
            }

            $parts[] = '&form%5B' . $key . '%5D=' . urlencode((string) $val);
        }

        return implode('', $parts);
    }

    private function loadKonf(int $kid): DfxKonf
    {
        $konf = $this->em->getRepository(DfxKonf::class)->find($kid);
        if ($konf === null) {
            throw $this->createNotFoundException('Kein Account gefunden für KalenderID ' . $kid);
        }

        return $konf;
    }

    private function loadArtikel(int $id): DfxNews
    {
        $artikel = $this->em->getRepository(DfxNews::class)->find($id);
        if ($artikel === null) {
            throw new GoneHttpException('Artikel nicht mehr vorhanden');
        }

        return $artikel;
    }

    private function corsJsonResponse(mixed $data): JsonResponse
    {
        $response = new JsonResponse($data);
        $sender = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '*';
        $response->headers->add([
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Origin' => $sender,
        ]);

        return $response;
    }
}
