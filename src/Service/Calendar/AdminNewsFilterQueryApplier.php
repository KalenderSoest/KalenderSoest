<?php

namespace App\Service\Calendar;

use Doctrine\ORM\QueryBuilder;

final class AdminNewsFilterQueryApplier
{
    public function apply(QueryBuilder $query, AdminNewsFilterData $filter, string $alias = 'n'): void
    {
        if ($filter->rubrik !== null && $filter->rubrik !== '') {
            $query->andWhere($alias . '.rubrik LIKE :rubrik')
                ->setParameter('rubrik', $this->jsonArrayContainsPattern($filter->rubrik));
        }

        if ($filter->zielgruppe !== null && $filter->zielgruppe !== '') {
            $query->andWhere($alias . '.zielgruppe LIKE :zielgruppe')
                ->setParameter('zielgruppe', $this->jsonArrayContainsPattern($filter->zielgruppe));
        }

        foreach ($filter->searchTerms() as $index => $term) {
            $param = 'suchwort' . ($index + 1);
            $query->andWhere("CONCAT(COALESCE($alias.titel,'_'),COALESCE($alias.beschreibung,'_'),COALESCE($alias.ort,'_'),COALESCE($alias.lokal,'_'),COALESCE($alias.veranstalter,'_')) LIKE :$param")
                ->setParameter($param, '%' . $term . '%');
        }
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }
}
