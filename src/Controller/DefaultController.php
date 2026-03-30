<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Service\Analytics\UsageTrackingService;
use App\Service\Frontend\FrontendBridgeService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\TemplatePathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class DefaultController extends AbstractController
{
    public function __construct(private readonly TemplatePathResolver $templatePathResolver, private readonly HtmlResponseService $htmlResponseService, private readonly UsageTrackingService $usageTrackingService, private readonly FrontendBridgeService $frontendBridgeService, private readonly EntityManagerInterface $em)
    {
    }

    #[Template("DfxFrontend/index.html.twig")]
    #[Route(path: '/', name: 'home')]
    public function index(Request $request): Response
    {
        $konf = $this->em->getRepository(DfxKonf::class)->find(1);
        if ($konf === null) {
            throw $this->createNotFoundException('Kein Account gefunden für KalenderID 1');
        }
        return $this->kalender($konf, $request);
    }

    #[Template("DfxFrontend/index.html.twig")]
    #[Route(path: '/kalender/{kid}', name: 'kalender_fe', defaults: ['kid' => 1], methods: ['GET', 'POST'])]
    public function kalender(#[MapEntity(id: 'kid')] DfxKonf $konf, Request $request): Response
    {
        $bridgeResult = $this->frontendBridgeService->renderContent($konf, $request);
        $arMenue = $this->usageTrackingService->getMenu($konf);
        $tpl = $this->templatePathResolver->resolve('DfxFrontend','index.html.twig', $konf);
        $options = ['dfx_content' => $bridgeResult['content'], 'konf' => $konf,  'arMenue' => $arMenue, 'termin' => $bridgeResult['termin'], 'artikel' => $bridgeResult['artikel']];
        return $this->htmlResponseService->render($tpl, $options);


    }

    #[Template("DfxFrontend/index.html.twig")]
    #[Route(path: '/news/{kid}', name: 'news_fe', defaults: ['kid' => 1], methods: ['GET', 'POST'])]
    public function news(#[MapEntity(id: 'kid')] DfxKonf $konf, Request $request): Response
    {
        $request->query->set('nfx', 'true');

        $bridgeResult = $this->frontendBridgeService->renderContent($konf, $request);
        $arMenue = $this->usageTrackingService->getMenu($konf);
        $tpl = $this->templatePathResolver->resolve('DfxFrontend','index.html.twig', $konf);
        $options = ['dfx_content' => $bridgeResult['content'], 'konf' => $konf,  'arMenue' => $arMenue, 'termin' => $bridgeResult['termin'], 'artikel' => $bridgeResult['artikel']];

        return $this->htmlResponseService->render($tpl, $options);
    }


    /**
     * @param $kid
     * @return array
     */
    #[Template("DfxFrontend/install.html.twig")]
    #[Route(path: '/install', name: 'dfx_install', methods: ['GET'])]
    public function install(): RedirectResponse
    {
        return $this->redirectToRoute('dfx_install_status');
    }



}
