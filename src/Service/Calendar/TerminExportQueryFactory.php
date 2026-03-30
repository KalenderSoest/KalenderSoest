<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class TerminExportQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly CommonTerminFilterQueryApplier $commonTerminFilterQueryApplier,
    ) {
    }

    /**
     * @return array{query: QueryBuilder, header: string}
     */
    public function build(DfxKonf $konf, array $data): array
    {
        $calendarScope = !empty($data['exportSub'])
            ? $this->calendarScopeResolver->resolveReadScope($konf)
            : $this->calendarScopeResolver->resolveAdminReadScope($konf, true, false);

        $query = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t']);

        $params = [];
        $header = '';

        if ($calendarScope->restrictsResults()) {
            $query->where('t.datefix IN (:kids)');
            $params['kids'] = $calendarScope->ids();
        }

        if (!empty($data['exportSub'])) {
            $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 't', $konf, false);
        }

        $commonFilter = new CommonTerminFilterData(
            rubrik: $data['rubrik'] ?? null,
            zielgruppe: $data['zielgruppe'] ?? null,
            ort: $data['ort'] ?? null,
            nat: $data['nat'] ?? null,
            veranstalter: $data['veranstalter'] ?? null,
            lokal: $data['lokal'] ?? null,
            plz: $data['plz'] ?? null,
            suche: $data['suche'] ?? null,
            datumVon: $data['datum_von'] ?? null,
            datumBis: $data['datum_bis'] ?? null,
            region: $data['region'] ?? null,
            filter1: !empty($data['filter1']),
            exactMatch: true,
        );

        foreach ([
            $commonFilter->rubrik,
            $commonFilter->zielgruppe,
            $commonFilter->veranstalter,
            $commonFilter->lokal,
            $commonFilter->nat,
            $commonFilter->ort,
        ] as $label) {
            if (!empty($label)) {
                $header .= $label . ' ';
            }
        }

        if (!empty($commonFilter->plz)) {
            $header .= 'im Postleitzahlgebiet ' . $commonFilter->plz . ' ';
        }
        if ($commonFilter->region !== null) {
            $header .= 'Region ' . $commonFilter->region->getRegion() . ' ';
        }
        if ($commonFilter->filter1) {
            $header .= 'Filter 1 gesetzt ';
        }
        if (!empty($commonFilter->suche)) {
            $header .= 'mit Suche "' . $commonFilter->suche . '" ';
        }

        $this->commonTerminFilterQueryApplier->apply($query, $commonFilter);

        $exportflag = $data['exportflag'] ?? null;
        if (!empty($exportflag)) {
            $header .= 'Exportflag ' . $exportflag . ' gesetzt ';
            $query->andWhere('t.exportflag IS NULL OR t.exportflag != :exportflag');
            $params['exportflag'] = $exportflag;
        }

        $datumCreated = $data['datum_created'] ?? null;
        if ($datumCreated !== null) {
            $header .= 'Eingabe ab ' . $datumCreated->format('d.m.Y');
            $query->andWhere('t.datumInput >= :created');
            $params['created'] = $datumCreated;
        }

        if ($commonFilter->datumVon === null && $commonFilter->datumBis === null) {
            $header .= 'ab ' . date('j.n.Y');
            $query->andWhere('t.datum >= CURRENT_DATE()');
        } else {
            if ($commonFilter->datumVon !== null && $commonFilter->datumBis !== null) {
                if ($commonFilter->datumVon->format('Y-m-d') === $commonFilter->datumBis->format('Y-m-d')) {
                    $header .= 'am ' . $commonFilter->datumVon->format('d.m.Y');
                } else {
                    $header .= 'von ' . $commonFilter->datumVon->format('d.m.Y') . ' bis ' . $commonFilter->datumBis->format('d.m.Y');
                }
            } elseif ($commonFilter->datumVon !== null) {
                $header .= 'ab ' . $commonFilter->datumVon->format('d.m.Y');
            } elseif ($commonFilter->datumBis !== null) {
                $header .= 'bis ' . $commonFilter->datumBis->format('d.m.Y');
            }
        }

        foreach ($params as $name => $value) {
            $query->setParameter($name, $value);
        }

        return ['query' => $query, 'header' => $header];
    }
}
