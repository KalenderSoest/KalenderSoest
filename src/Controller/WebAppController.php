<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxRegion;
use App\Service\Calendar\CalendarFieldChoiceProvider;
use App\Service\Calendar\CalendarScopeResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebAppController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly CalendarFieldChoiceProvider $calendarFieldChoiceProvider,
    ) {
    }

    #[Route(path: '/webapp/', name: 'webapp_index', defaults: ['kid' => 1], methods: ['GET'])]
    #[Route(path: '/webapp', methods: ['GET'])]
    #[Route(path: '/webapp/{kid}', name: 'webapp_index_kid', requirements: ['kid' => '\\d+'], methods: ['GET'])]
    public function index(#[MapEntity(id: 'kid')] DfxKonf $konf): Response
    {
        $kid = $konf->getId();

        $webappConfig = [
            'kid' => $kid,
            'title' => $konf->getTitel() ?? 'Datefix',
            'allowApi' => $konf->getAllowApi(),
            'pageApiItems' => $konf->getPageApiItems() ?? 20,
            'maxApiItems' => $konf->getMaxApiItems() ?? 1000,
            'mapkey' => $this->getParameter('mapkey'),
            'tileserver' => $this->getParameter('tileserver'),
            'mapset' => $this->getParameter('mapset'),
            'copyright' => $this->getParameter('copyright'),
        ];

        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $rubriken = is_array($konf->getRubriken()) ? array_values(array_filter($konf->getRubriken())) : [];
        $zielgruppen = is_array($konf->getZielgruppen()) ? array_values(array_filter($konf->getZielgruppen())) : [];
        sort($rubriken);
        sort($zielgruppen);

        $regions = [];
        $regionQuery = $this->em->getRepository(DfxRegion::class)->createQueryBuilder('r')->select(['r']);
        if ($calendarScope->restrictsResults()) {
            $regionQuery->where('r.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        }
        foreach ($regionQuery->orderBy('r.region', 'ASC')->getQuery()->getResult() as $region) {
            if (!$region instanceof DfxRegion || $region->getId() === null || $region->getRegion() === null) {
                continue;
            }
            $regions[] = ['id' => $region->getId(), 'label' => $region->getRegion()];
        }

        $webappCalendarFilters = [
            'showCalendar' => (bool) $konf->getFilterKalender(),
            'showDate' => (bool) $konf->getFilterDatum(),
            'showRubrik' => (bool) $konf->getFilterRubrik(),
            'showZielgruppe' => (bool) $konf->getFilterZielgruppe(),
            'showSearch' => (bool) $konf->getFilterSuche(),
            'showNat' => (bool) $konf->getFilterNat(),
            'showRegion' => (bool) $konf->getFilterRegion(),
            'showOrt' => (bool) $konf->getFilterOrt(),
            'showLocation' => (bool) $konf->getFilterLocation(),
            'showVeranstalter' => (bool) $konf->getFilterVeranstalter(),
            'showPlzArea' => (bool) $konf->getFilterPlzarea(),
            'showRadius' => (bool) $konf->getFilterUmkreis(),
            'rubriken' => $rubriken,
            'zielgruppen' => $zielgruppen,
            'natOptions' => array_keys($this->calendarFieldChoiceProvider->forScope($calendarScope, 'nat')),
            'ortOptions' => array_keys($this->calendarFieldChoiceProvider->forScope($calendarScope, 'ort')),
            'lokalOptions' => array_keys($this->calendarFieldChoiceProvider->forScope($calendarScope, 'lokal')),
            'veranstalterOptions' => array_keys($this->calendarFieldChoiceProvider->forScope($calendarScope, 'veranstalter')),
            'regions' => $regions,
            'currentMonth' => date('Y-m'),
        ];

        return $this->render('webapp/index.html.twig', [
            'konf' => $konf,
            'webappConfig' => $webappConfig,
            'webappCalendarFilters' => $webappCalendarFilters,
        ]);
    }

}
