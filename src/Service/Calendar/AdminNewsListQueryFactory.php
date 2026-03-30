<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class AdminNewsListQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly AdminNewsFilterQueryApplier $adminNewsFilterQueryApplier,
    ) {
    }

    /**
     * @return array{
     *   published: QueryBuilder,
     *   unpublished: QueryBuilder,
     *   metaPending: QueryBuilder,
     *   groupPending: QueryBuilder,
     *   archived: QueryBuilder
     * }
     */
    public function build(
        DfxKonf $konf,
        CalendarScope $calendarScope,
        AdminNewsFilterData $filterData,
        ?int $userId = null,
    ): array {
        $baseQuery = $this->em
            ->getRepository(DfxNews::class)
            ->createQueryBuilder('n')
            ->select('partial n.{id,datumVon, datumBis, titel, pub, pubMeta, pubGroup, code,counter}');

        if ($calendarScope->restrictsResults()) {
            $baseQuery
                ->where('n.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        } else {
            $baseQuery->where('n.datefix IS NOT NULL');
        }

        $this->calendarPublicationQueryHelper->applyPendingSharedVisibility($baseQuery, 'n', $konf, $filterData->filterPub);
        $this->adminNewsFilterQueryApplier->apply($baseQuery, $filterData);

        if ($userId !== null) {
            $baseQuery
                ->andWhere('n.user = :uid')
                ->setParameter('uid', $userId);
        }

        $published = clone $baseQuery;
        $published
            ->andWhere('n.datumBis IS NULL OR n.datumBis >= CURRENT_DATE()')
            ->andWhere('n.pub = true')
            ->orderBy('n.datumInput', 'DESC');

        $unpublished = clone $baseQuery;
        $unpublished
            ->andWhere('n.datumBis IS NULL OR n.datumBis >= CURRENT_DATE()')
            ->andWhere('n.pub = false OR n.pub IS NULL')
            ->orderBy('n.datumInput', 'DESC');

        $metaPending = clone $baseQuery;
        $metaPending->andWhere('n.datumBis IS NULL OR n.datumBis >= CURRENT_DATE()');
        $this->calendarPublicationQueryHelper->applyPendingSharedVisibility($metaPending, 'n', $konf, true);
        $metaPending->orderBy('n.datumInput', 'DESC');

        $groupPending = clone $baseQuery;
        $groupPending
            ->andWhere('n.datumBis IS NULL OR n.datumBis >= CURRENT_DATE()')
            ->andWhere('n.pubGroup IS NULL')
            ->orderBy('n.datumInput', 'DESC');

        $archived = clone $baseQuery;
        $archived
            ->andWhere('n.datumBis < CURRENT_DATE()')
            ->orderBy('n.datumBis', 'DESC');

        return [
            'published' => $published,
            'unpublished' => $unpublished,
            'metaPending' => $metaPending,
            'groupPending' => $groupPending,
            'archived' => $archived,
        ];
    }
}
