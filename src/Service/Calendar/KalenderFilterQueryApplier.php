<?php

namespace App\Service\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

final class KalenderFilterQueryApplier
{
    public function applySharedFilters(QueryBuilder $query, KalenderFilterData $filter, string $alias = 't'): void
    {
        if ($filter->rubrik !== null && $filter->rubrik !== '') {
            $query->andWhere($alias . '.rubrik LIKE :rubrik')
                ->setParameter('rubrik', $this->jsonArrayContainsPattern($filter->rubrik));
        }

        if ($filter->zielgruppe !== null && $filter->zielgruppe !== '') {
            $query->andWhere($alias . '.zielgruppe LIKE :zielgruppe')
                ->setParameter('zielgruppe', $this->jsonArrayContainsPattern($filter->zielgruppe));
        }

        for ($i = 1; $i <= 5; $i++) {
            if ($filter->filterEnabled($i)) {
                $query->andWhere($alias . '.filter' . $i . ' = 1');
            }
        }

        if ($filter->veranstalter !== null && $filter->veranstalter !== '') {
            $query->andWhere($alias . '.veranstalter = :veranstalter')
                ->setParameter('veranstalter', $filter->veranstalter);
        }

        if ($filter->idVeranstalter !== null && $filter->idVeranstalter !== '') {
            $query->andWhere($alias . '.idVeranstalter = :idVeranstalter')
                ->setParameter('idVeranstalter', $filter->idVeranstalter);
        }

        if ($filter->lokal !== null && $filter->lokal !== '') {
            $query->andWhere($alias . '.lokal = :lokal')
                ->setParameter('lokal', $filter->lokal);
        }

        if ($filter->idLocation !== null && $filter->idLocation !== '') {
            $query->andWhere($alias . '.idLocation = :idLocation')
                ->setParameter('idLocation', $filter->idLocation);
        }

        if ($filter->hasRadiusSearch()) {
            $query->andWhere('(6371 * acos(cos(radians(:bg)) * cos(radians(' . $alias . '.bg)) * cos(radians(' . $alias . '.lg) - radians(:lg)) + sin(radians(:bg)) * sin(radians(' . $alias . '.bg)))) <= :umkreis')
                ->setParameter('bg', $filter->bg)
                ->setParameter('lg', $filter->lg)
                ->setParameter('umkreis', (int) $filter->umkreis);
        } else {
            if ($filter->nat !== null && $filter->nat !== '') {
                $query->andWhere($alias . '.nat = :nat')
                    ->setParameter('nat', $filter->nat);
            }

            if ($filter->plz !== null && $filter->plz !== '') {
                $query->andWhere($alias . '.plz LIKE :plz')
                    ->setParameter('plz', $filter->plz . '%');
            }

            if ($filter->ort !== null && $filter->ort !== '') {
                $query->andWhere($alias . '.ort = :ort')
                    ->setParameter('ort', $filter->ort);
            }

            if ($filter->region !== null && $filter->region !== '') {
                $query->andWhere($alias . '.region = :region')
                    ->setParameter('region', $filter->region);
            }
        }

        $searchTerms = $filter->searchTerms();
        if ($searchTerms !== []) {
            foreach ($searchTerms as $index => $term) {
                $param = 'suchwort' . ($index + 1);
                $query->andWhere("CONCAT(COALESCE($alias.titel,'_'), COALESCE($alias.subtitel,'_'), COALESCE($alias.beschreibung,'_'), COALESCE($alias.ort,'_'), COALESCE($alias.lokal,'_'), COALESCE($alias.veranstalter,'_')) LIKE :$param")
                    ->setParameter($param, '%' . $term . '%');
            }
        }
    }

    /**
     * @return array{month:int, year:int}
     */
    public function applyCalendarMonth(QueryBuilder $query, KalenderFilterData $filter, string $alias = 't'): array
    {
        if ($filter->t !== null && $filter->t !== '') {
            [$year, $month] = $this->splitMonthString($filter->t);
        } elseif ($filter->m !== null && $filter->m !== '') {
            [$year, $month] = $this->splitMonthString($filter->m);
        } elseif ($filter->datumVon instanceof DateTimeInterface) {
            $year = (int) $filter->datumVon->format('Y');
            $month = (int) $filter->datumVon->format('m');
        } else {
            $year = (int) date('Y');
            $month = (int) date('m');
        }

        $query->andWhere('MONTH(' . $alias . '.datumVon) = :monat AND YEAR(' . $alias . '.datumVon) = :jahr')
            ->setParameter('monat', $month)
            ->setParameter('jahr', $year);

        return ['month' => $month, 'year' => $year];
    }

    public function applyListDateWindow(QueryBuilder $query, KalenderFilterData $filter, string $alias = 't'): void
    {
        if ($filter->t !== null && $filter->t !== '') {
            $query->andWhere($alias . '.datumVon = :tag')
                ->setParameter('tag', $filter->t);
            return;
        }

        if (
            $filter->datumVon === null
            && $filter->datumBis === null
            && ($filter->m === null || $filter->m === '')
        ) {
            $query->andWhere($alias . '.datumVon >= CURRENT_DATE()');
            return;
        }

        if ($filter->datumVon instanceof DateTimeInterface && $filter->datumBis instanceof DateTimeInterface) {
            if ($filter->datumVon->format('Y-m-d') === $filter->datumBis->format('Y-m-d')) {
                $query->andWhere($alias . '.datumVon = :tag')
                    ->setParameter('tag', $filter->datumVon);
                return;
            }

            $query->andWhere($alias . '.datumVon BETWEEN :tag_von AND :tag_bis')
                ->setParameter('tag_von', $filter->datumVon)
                ->setParameter('tag_bis', $filter->datumBis);
            return;
        }

        if ($filter->datumVon instanceof DateTimeInterface) {
            $query->andWhere($alias . '.datumVon >= :tag_von')
                ->setParameter('tag_von', $filter->datumVon);
            return;
        }

        if ($filter->datumBis instanceof DateTimeInterface) {
            $query->andWhere($alias . '.datumVon <= :tag_bis')
                ->setParameter('tag_bis', $filter->datumBis);
            return;
        }

        if ($filter->m !== null && $filter->m !== '') {
            [$year, $month] = $this->splitMonthString($filter->m);
            $query->andWhere('MONTH(' . $alias . '.datumVon) = :monat AND YEAR(' . $alias . '.datumVon)= :jahr')
                ->setParameter('monat', $month)
                ->setParameter('jahr', $year);
        }
    }

    /**
     * @return array{0:int,1:int}
     */
    private function splitMonthString(string $value): array
    {
        if (preg_match('/^(?<year>\d{4})-(?<month>\d{2})(?:-\d{2})?$/', $value, $matches) !== 1) {
            return [(int) date('Y'), (int) date('m')];
        }

        return [(int) $matches['year'], (int) $matches['month']];
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }
}
