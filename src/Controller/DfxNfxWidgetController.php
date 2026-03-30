<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxBox;
use App\Service\Calendar\WidgetNewsQueryFactory;
use App\Service\Calendar\WidgetTerminQueryFactory;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\TemplatePathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class DfxNfxWidgetController extends AbstractController
{
    public function __construct(
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly WidgetTerminQueryFactory $widgetTerminQueryFactory,
        private readonly WidgetNewsQueryFactory $widgetNewsQueryFactory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/widgets/terminbox/{kid}', name: 'terminbox', methods: ['GET'])]
    public function terminbox(#[MapEntity(id: 'kid')] DfxKonf $konf, Request $request): Response
    {
        $kid = $konf->getId();
    	$box = $this->em->getRepository(DfxBox::class)->findBy(['datefix' => $kid]);
    	if (!$box) {
    		throw $this->createNotFoundException(
    				'Kein Account gefunden für KalenderID '.$kid  //.var_dump($box)
    		);
    	}
        
    	$box=$box[0];

        // überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxNfxWidgets','terminbox.html.twig', $konf);

    	$termine = $this->widgetTerminQueryFactory->buildTerminboxQuery($konf, $box, $request)->getQuery()->getResult();
    	$options = ['termine' => $termine, 'konf'=>$box, 'datefix' => $konf];
    	return $this->htmlResponseService->render($tpl, $options);
     }

    #[Route(path: '/widgets/newsbox/{kid}', name: 'newsbox', methods: ['GET'])]
    public function newsbox(#[MapEntity(id: 'kid')] DfxKonf $konf, Request $request): Response
    {
        $kid = $konf->getId();
        $box = $this->em->getRepository(DfxBox::class)->findBy(['datefix' => $kid]);
        if (!$box) {
            throw $this->createNotFoundException('Kein Account gefunden für KalenderID ' . $kid);
        }

        $box = $box[0];
        $tpl = $this->templatePathResolver->resolve('DfxNfxWidgets', 'newsbox.html.twig', $konf);
        $artikel = $this->widgetNewsQueryFactory->buildNewsboxQuery($konf, $box, $request)->getQuery()->getResult();
        $options = ['artikel' => $artikel, 'konf' => $box, 'datefix' => $konf];

        return $this->htmlResponseService->render($tpl, $options);
    }
}
