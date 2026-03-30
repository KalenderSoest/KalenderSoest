<?php

namespace App\Service\Calendar;

use Doctrine\ORM\QueryBuilder;

final class AdminTermineFilterQueryApplier
{
    public function __construct(
        private readonly CommonTerminFilterQueryApplier $commonTerminFilterQueryApplier,
    ) {
    }

    public function apply(QueryBuilder $query, AdminTermineFilterData $filter, string $alias = 't'): void
    {
        $this->commonTerminFilterQueryApplier->apply($query, new CommonTerminFilterData(
            rubrik: $filter->rubrik,
            zielgruppe: $filter->zielgruppe,
            ort: $filter->ort,
            nat: $filter->nat,
            veranstalter: $filter->veranstalter,
            lokal: $filter->lokal,
            plz: $filter->plz,
            suche: $filter->suche,
            datumVon: $filter->datumVon,
            datumBis: $filter->datumBis,
            optionsRadio: $filter->optionsRadio,
            exactMatch: false,
        ), $alias);
    }
}
