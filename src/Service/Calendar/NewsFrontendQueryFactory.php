<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class NewsFrontendQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
    ) {
    }

    /**
     * @return array{query: QueryBuilder, header: string, filter: array<string, mixed>}
     */
    public function build(DfxKonf $konf, NewsFrontendFilterData $filterData): array
    {
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $query = $this->em->getRepository(DfxNews::class)
            ->createQueryBuilder('n')
            ->select(['n'])
            ->where('n.newsTyp = :newstyp');

        $params = ['newstyp' => 'beitrag'];
        $filter = [];
        $header = '';

        if ($calendarScope->restrictsResults()) {
            $query->andWhere('n.datefix IN (:kids)');
            $params['kids'] = $calendarScope->ids();
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 'n', $konf);

        if (!empty($filterData->rubrik)) {
            $header .= $filterData->rubrik . ' ';
            $query->andWhere('n.rubrik LIKE :rubrik');
            $params['rubrik'] = $this->jsonArrayContainsPattern($filterData->rubrik);
            $filter['rubrik'] = $filterData->rubrik;
        }

        if (!empty($filterData->zielgruppe)) {
            $header .= $filterData->zielgruppe . ' ';
            $query->andWhere('n.zielgruppe LIKE :zielgruppe');
            $params['zielgruppe'] = $this->jsonArrayContainsPattern($filterData->zielgruppe);
            $filter['zielgruppe'] = $filterData->zielgruppe;
        }

        foreach ([1, 2, 3, 4, 5] as $index) {
            $property = 'filter' . $index;
            if ($filterData->{$property}) {
                $query->andWhere('n.' . $property . ' = 1');
                $filter[$property] = 1;
            }
        }

        if (!empty($filterData->suche)) {
            $header .= 'mit Suchwort(en)  "' . $filterData->suche . '" ';
            $suchworte = explode(' ', $filterData->suche);
            $counter = 1;
            foreach ($suchworte as $suchwort) {
                $query->andWhere("CONCAT(COALESCE(n.titel,'_'), COALESCE(n.subtitel,'_'), COALESCE(n.beschreibung,'_'),COALESCE(n.ort,'_'),COALESCE(n.lokal,'_'),COALESCE(n.veranstalter,'_')) LIKE :suchwort" . $counter);
                $params['suchwort' . $counter] = '%' . $suchwort . '%';
                $counter++;
            }
            $filter['suche'] = $filterData->suche;
        }

        if ($filterData->datumVon !== null && $filterData->datumBis === null) {
            $header .= 'ab ' . $filterData->datumVon->format('d.m.Y');
            $query->andWhere('n.datumVon >= :tag_von');
            $params['tag_von'] = $filterData->datumVon;
        } elseif ($filterData->datumVon !== null && $filterData->datumBis !== null) {
            $header .= 'vom ' . $filterData->datumVon->format('d.m.Y') . ' bis ' . $filterData->datumBis->format('d.m.Y');
            $query->andWhere('n.datumVon BETWEEN :tag_von AND :tag_bis');
            $params['tag_von'] = $filterData->datumVon;
            $params['tag_bis'] = $filterData->datumBis;
        } else {
            $query->andWhere('n.datumBis IS NULL OR n.datumBis >= CURRENT_DATE()');
        }

        if ($filterData->datumVon !== null) {
            $filter['datum_von'] = $filterData->datumVon->format('Y-m-d');
        }
        if ($filterData->datumBis !== null) {
            $filter['datum_bis'] = $filterData->datumBis->format('Y-m-d');
        }

        foreach ($params as $key => $value) {
            $query->setParameter($key, $value);
        }

        $query->orderBy('n.datumVon', 'DESC');

        return [
            'query' => $query,
            'header' => $header,
            'filter' => $filter,
        ];
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }
}
