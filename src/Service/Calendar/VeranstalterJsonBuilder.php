<?php

namespace App\Service\Calendar;

use App\Entity\DfxVeranstalter;

final class VeranstalterJsonBuilder
{
    /**
     * @return array<string, int|string|null>
     */
    public function build(DfxVeranstalter $entity): array
    {
        $payload = [
            'veranstalter' => $entity->getName(),
            'mail' => $entity->getEmail(),
        ];

        if ($entity->getLocation() !== null) {
            $payload['idLocation'] = $entity->getLocation()->getId();
        }

        return $payload;
    }
}
