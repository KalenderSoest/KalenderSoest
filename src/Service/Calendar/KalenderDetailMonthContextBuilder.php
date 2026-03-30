<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;

final class KalenderDetailMonthContextBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly KalenderFilterQueryApplier $kalenderFilterQueryApplier,
        private readonly KalenderMonthViewBuilder $kalenderMonthViewBuilder,
    ) {
    }

    public function build(DfxKonf $konf, int $kid, DfxTermine $termin, array $queryParams): array
    {
        $queryKal = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t.datumVon']);
        $queryKal->where('t.datefix = :kid')
            ->setParameter('kid', $kid);
        $this->calendarPublicationQueryHelper->applyPublishedVisibility($queryKal, 't', $konf);

        $detailFilterData = new KalenderFilterData(
            null,
            null,
            [],
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $termin->getDatumVon()->format('Y-m'),
            null,
            null,
            null,
        );

        $monthSelection = $this->kalenderFilterQueryApplier->applyCalendarMonth($queryKal, $detailFilterData);
        $queryKal->groupBy('t.datumVon')
            ->orderBy('t.datumVon', 'ASC');
        $tageKal = $queryKal->getQuery()->getArrayResult();

        $kalJahr = $monthSelection['year'] ?? (int) $termin->getDatumVon()->format('Y');
        $kalMonat = $monthSelection['month'] ?? (int) $termin->getDatumVon()->format('m');

        return $this->kalenderMonthViewBuilder->build(
            $tageKal,
            $kalJahr,
            $kalMonat,
            $konf->getFrontendUrl(),
            $queryParams,
        );
    }
}
