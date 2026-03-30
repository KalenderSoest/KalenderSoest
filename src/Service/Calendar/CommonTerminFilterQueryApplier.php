<?php

namespace App\Service\Calendar;

use Doctrine\ORM\QueryBuilder;

final class CommonTerminFilterQueryApplier
{
    public function apply(QueryBuilder $query, CommonTerminFilterData $filter, string $alias = 't'): void
    {
        if ($filter->rubrik !== null && $filter->rubrik !== '') {
            $query->andWhere($alias . '.rubrik LIKE :rubrik')
                ->setParameter('rubrik', $this->jsonArrayContainsPattern($filter->rubrik));
        }

        if ($filter->zielgruppe !== null && $filter->zielgruppe !== '') {
            $query->andWhere($alias . '.zielgruppe LIKE :zielgruppe')
                ->setParameter('zielgruppe', $this->jsonArrayContainsPattern($filter->zielgruppe));
        }

        foreach (['ort', 'nat', 'veranstalter', 'lokal'] as $field) {
            $value = $filter->{$field};
            if ($value === null || $value === '') {
                continue;
            }

            $operator = $filter->exactMatch ? '=' : 'LIKE';
            $parameter = $filter->exactMatch ? $value : '%' . $value . '%';
            $query->andWhere($alias . '.' . $field . ' ' . $operator . ' :' . $field)
                ->setParameter($field, $parameter);
        }

        if ($filter->plz !== null && $filter->plz !== '') {
            $parameter = $filter->exactMatch ? $filter->plz : '%' . $filter->plz . '%';
            $query->andWhere($alias . '.plz LIKE :plz')
                ->setParameter('plz', $parameter);
        }

        if ($filter->region !== null) {
            $query->andWhere($alias . '.region = :region')
                ->setParameter('region', $filter->region);
        }

        if ($filter->optionsRadio !== null) {
            $query->andWhere($alias . '.optionsRadio = :optionsRadio')
                ->setParameter('optionsRadio', $filter->optionsRadio);
        }

        if ($filter->filter1) {
            $query->andWhere($alias . '.filter1 = 1');
        }

        foreach ($filter->searchTerms() as $index => $term) {
            $param = 'suchwort' . ($index + 1);
            $query->andWhere("CONCAT(COALESCE($alias.titel,'_'), COALESCE($alias.subtitel,'_'), COALESCE($alias.beschreibung,'_'), COALESCE($alias.ort,'_'), COALESCE($alias.lokal,'_'), COALESCE($alias.veranstalter,'_')) LIKE :$param")
                ->setParameter($param, '%' . $term . '%');
        }

        if ($filter->datumVon !== null && $filter->datumBis !== null) {
            if ($filter->datumVon->format('Y-m-d') === $filter->datumBis->format('Y-m-d')) {
                $query->andWhere('(:tag BETWEEN ' . $alias . '.datumVon AND ' . $alias . '.datum)')
                    ->setParameter('tag', $filter->datumVon);

                return;
            }

            $query->andWhere('(' . $alias . '.datumVon = ' . $alias . '.datum AND ' . $alias . '.datum BETWEEN :tag_von AND :tag_bis) OR (' . $alias . '.datumVon != ' . $alias . '.datum AND (:tag_von BETWEEN ' . $alias . '.datumVon AND ' . $alias . '.datum OR :tag_bis BETWEEN ' . $alias . '.datumVon AND ' . $alias . '.datum OR (' . $alias . '.datumVon BETWEEN :tag_von AND :tag_bis AND ' . $alias . '.datum BETWEEN :tag_von AND :tag_bis)))')
                ->setParameter('tag_von', $filter->datumVon)
                ->setParameter('tag_bis', $filter->datumBis);

            return;
        }

        if ($filter->datumVon !== null) {
            $query->andWhere($alias . '.datum >= :tag_von')
                ->setParameter('tag_von', $filter->datumVon);

            return;
        }

        if ($filter->datumBis !== null) {
            $query->andWhere($alias . '.datumVon <= :tag_bis')
                ->setParameter('tag_bis', $filter->datumBis);
        }
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }
}
