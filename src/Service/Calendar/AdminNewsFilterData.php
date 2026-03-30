<?php

namespace App\Service\Calendar;

final class AdminNewsFilterData
{
    public function __construct(
        public readonly ?string $rubrik,
        public readonly ?string $zielgruppe,
        public readonly ?string $suche,
        public readonly bool $hideSub,
        public readonly bool $filterPub,
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
