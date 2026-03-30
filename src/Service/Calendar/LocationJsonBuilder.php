<?php

namespace App\Service\Calendar;

use App\Entity\DfxLocation;

final class LocationJsonBuilder
{
    /**
     * @return array<string, int|float|string|null>
     */
    public function build(DfxLocation $entity): array
    {
        $payload = [
            'lokal' => $entity->getName(),
            'lokalStrasse' => $entity->getStrasse(),
            'nat' => $entity->getNat(),
            'plz' => $entity->getPlz(),
            'ort' => $entity->getOrt(),
            'lg' => $entity->getLg(),
            'bg' => $entity->getBg(),
        ];

        if ($entity->getVeranstalter() !== null) {
            $payload['idVeranstalter'] = $entity->getVeranstalter()->getId();
        }

        return $payload;
    }
}
