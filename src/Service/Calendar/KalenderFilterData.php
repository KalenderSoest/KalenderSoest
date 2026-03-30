<?php

namespace App\Service\Calendar;

use DateTimeInterface;

final class KalenderFilterData
{
    /**
     * @param array<int, bool> $flags
     */
    public function __construct(
        public readonly ?string $rubrik,
        public readonly ?string $zielgruppe,
        public readonly array $flags,
        public readonly ?string $veranstalter,
        public readonly mixed $idVeranstalter,
        public readonly ?string $lokal,
        public readonly mixed $idLocation,
        public readonly ?string $umkreis,
        public readonly ?string $plz,
        public readonly ?string $ort,
        public readonly ?float $bg,
        public readonly ?float $lg,
        public readonly ?string $nat,
        public readonly mixed $region,
        public readonly ?string $suche,
        public readonly ?string $m,
        public readonly ?string $t,
        public readonly ?DateTimeInterface $datumVon,
        public readonly ?DateTimeInterface $datumBis,
    ) {
    }

    public function filterEnabled(int $number): bool
    {
        return $this->flags[$number] ?? false;
    }

    public function hasRadiusSearch(): bool
    {
        return (int) ($this->umkreis ?? 0) > 0
            && ($this->bg ?? 0.0) > 0
            && ($this->lg ?? 0.0) > 0;
    }

    public function hasExplicitDateSelection(): bool
    {
        return ($this->t !== null && $this->t !== '')
            || ($this->m !== null && $this->m !== '')
            || $this->datumVon instanceof DateTimeInterface
            || $this->datumBis instanceof DateTimeInterface;
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
