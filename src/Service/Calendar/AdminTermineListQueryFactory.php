<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class AdminTermineListQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly AdminTermineFilterQueryApplier $adminTermineFilterQueryApplier,
    ) {
    }

    /**
     * @return array{
     *   published: QueryBuilder,
     *   unpublished: QueryBuilder,
     *   metaPending: QueryBuilder,
     *   groupPending: QueryBuilder,
     *   archived: QueryBuilder,
     *   series: QueryBuilder
     * }
     */
    public function build(
        DfxKonf $konf,
        CalendarScope $calendarScope,
        AdminTermineFilterData $filterData,
        ?int $userId = null,
    ): array {
        $baseQuery = $this->em
            ->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select('partial t.{id,datum, datumVon, zeit, zeitBis, titel, pub, pubMeta, pubGroup, code, optionsRadio, optionsCheckboxes, optionsMenue, optionsMenueMulti,counter}');

        if ($calendarScope->restrictsResults()) {
            $baseQuery
                ->where('t.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        } else {
            $baseQuery->where('t.datefix IS NOT NULL');
        }

        $this->calendarPublicationQueryHelper->applyPendingSharedVisibility($baseQuery, 't', $konf, $filterData->filterPub);
        $this->adminTermineFilterQueryApplier->apply($baseQuery, $filterData);

        if ($userId !== null) {
            $baseQuery
                ->andWhere('t.user = :uid')
                ->setParameter('uid', $userId);
        }

        $published = clone $baseQuery;
        $published
            ->andWhere('t.datum >= CURRENT_DATE()')
            ->andWhere('t.pub = true')
            ->orderBy('t.datumVon, t.zeit');

        $unpublished = clone $baseQuery;
        $unpublished
            ->andWhere('t.datum >= CURRENT_DATE()')
            ->andWhere('t.pub = false OR t.pub IS NULL')
            ->orderBy('t.datumVon,t.zeit');

        $metaPending = clone $baseQuery;
        $metaPending->andWhere('t.datum >= CURRENT_DATE()');
        $this->calendarPublicationQueryHelper->applyPendingSharedVisibility($metaPending, 't', $konf, true);
        $metaPending->orderBy('t.datumInput', 'DESC');

        $groupPending = clone $baseQuery;
        $groupPending
            ->andWhere('t.datum >= CURRENT_DATE()')
            ->andWhere('t.pubGroup IS NULL')
            ->orderBy('t.datumInput', 'DESC');

        $archived = clone $baseQuery;
        $archived
            ->andWhere('t.datum < CURRENT_DATE()')
            ->orderBy('t.datumVon, t.zeit');

        $series = clone $baseQuery;
        $series
            ->andWhere('t.datum >= CURRENT_DATE()')
            ->andWhere('t.datumSerie IS NOT NULL')
            ->orderBy('t.datumVon,t.zeit')
            ->groupBy('t.code');

        return [
            'published' => $published,
            'unpublished' => $unpublished,
            'metaPending' => $metaPending,
            'groupPending' => $groupPending,
            'archived' => $archived,
            'series' => $series,
        ];
    }
}
