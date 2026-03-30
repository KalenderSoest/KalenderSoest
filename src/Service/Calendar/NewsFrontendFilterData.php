<?php

namespace App\Service\Calendar;

use DateTimeInterface;

final class NewsFrontendFilterData
{
    public function __construct(
        public readonly ?string $rubrik,
        public readonly ?string $zielgruppe,
        public readonly bool $filter1,
        public readonly bool $filter2,
        public readonly bool $filter3,
        public readonly bool $filter4,
        public readonly bool $filter5,
        public readonly ?string $suche,
        public readonly ?DateTimeInterface $datumVon,
        public readonly ?DateTimeInterface $datumBis,
    ) {
    }
}
