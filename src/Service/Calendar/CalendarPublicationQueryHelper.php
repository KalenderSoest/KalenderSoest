<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use Doctrine\ORM\QueryBuilder;

final class CalendarPublicationQueryHelper
{
    public function applyScopePublishedVisibility(QueryBuilder $query, string $alias, CalendarScope $scope, bool $requireBasePublication = true): void
    {
        if ($requireBasePublication) {
            $query->andWhere($alias . '.pub = 1');
        }

        if ($scope->isMeta()) {
            $query->andWhere($alias . '.pubMeta = 1');

            return;
        }

        if ($scope->isGroup()) {
            $query->andWhere($alias . '.pubGroup = 1');
        }
    }

    public function applyPublishedVisibility(QueryBuilder $query, string $alias, DfxKonf $konf, bool $requireBasePublication = true): void
    {
        if ($requireBasePublication) {
            $query->andWhere($alias . '.pub = 1');
        }

        if ((int) $konf->getIsMeta() === 1) {
            $query->andWhere($alias . '.pubMeta = 1');

            return;
        }

        if ((int) $konf->getIsGroup() === 1) {
            $query->andWhere($alias . '.pubGroup = 1');
        }
    }

    public function applyPendingSharedVisibility(QueryBuilder $query, string $alias, DfxKonf $konf, bool $enabled): void
    {
        if (!$enabled) {
            return;
        }

        if ((int) $konf->getIsMeta() === 1) {
            $query->andWhere($alias . '.pubMeta = false OR ' . $alias . '.pubMeta IS NULL');

            return;
        }

        if ((int) $konf->getIsGroup() === 1) {
            $query->andWhere($alias . '.pubGroup = false OR ' . $alias . '.pubGroup IS NULL');
        }
    }
}
