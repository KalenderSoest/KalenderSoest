<?php

namespace App\Service\Calendar;

use App\Entity\DfxDozenten;

final class DozentenJsonBuilder
{
    /**
     * @return array<string, int|string|null>
     */
    public function build(DfxDozenten $entity): array
    {
        $payload = [
            'dozent' => $entity->getName(),
            'mail' => $entity->getEmail(),
        ];

        if ($entity->getLocation() !== null) {
            $payload['idLocation'] = $entity->getLocation()->getId();
        }

        return $payload;
    }
}
