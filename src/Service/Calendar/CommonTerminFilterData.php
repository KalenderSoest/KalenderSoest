<?php

namespace App\Service\Calendar;

use DateTimeInterface;

final class CommonTerminFilterData
{
    public function __construct(
        public readonly ?string $rubrik,
        public readonly ?string $zielgruppe,
        public readonly ?string $ort,
        public readonly ?string $nat,
        public readonly ?string $veranstalter,
        public readonly ?string $lokal,
        public readonly ?string $plz,
        public readonly ?string $suche,
        public readonly ?DateTimeInterface $datumVon,
        public readonly ?DateTimeInterface $datumBis,
        public readonly ?object $region = null,
        public readonly ?int $optionsRadio = null,
        public readonly bool $filter1 = false,
        public readonly bool $exactMatch = false,
    ) {
    }

    /**
     * @return list<string>
     */
    public function searchTerms(): array
    {
        if ($this->suche === null || trim($this->suche) === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', trim($this->suche))));
    }
}
