<?php

namespace App\Service\Api\Custom\Example;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use App\Service\Api\SchemaOrgApiPayloadRenderer;

/**
 * Beispiel fuer eine kundenspezifische API-Ausgabe.
 *
 * Aktivierung:
 * 1. Datei in eine der vom Resolver erwarteten Klassen verschieben:
 *    - `App\Service\Api\Custom\ApiPayloadRenderer`
 *    - `App\Service\Api\Custom\Kid{kid}\ApiPayloadRenderer`
 * 2. Danach kann die Standard-Ausgabe punktuell erweitert oder ersetzt werden.
 *
 * Dieses Beispiel ist bewusst NICHT aktiv, damit die laufende API-Ausgabe
 * unveraendert bleibt.
 */
class ApiPayloadRenderer extends SchemaOrgApiPayloadRenderer
{
    /**
     * @param list<DfxTermine> $entities
     *
     * @return list<array<string, mixed>>
     */
    public function renderTerminList(array $entities, DfxKonf $konf): array
    {
        $payload = parent::renderTerminList($entities, $konf);

        foreach ($payload as &$item) {
            $item['apiSource'] = 'custom-example';
            $item['calendarId'] = $konf->getId();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function renderTerminDetail(DfxTermine $entity): array
    {
        $payload = parent::renderTerminDetail($entity);
        $payload['apiSource'] = 'custom-example';

        return $payload;
    }

    /**
     * @param list<DfxNews> $entities
     *
     * @return list<array<string, mixed>>
     */
    public function renderNewsList(array $entities, DfxKonf $konf): array
    {
        $payload = parent::renderNewsList($entities, $konf);

        foreach ($payload as &$item) {
            $item['apiSource'] = 'custom-example';
            $item['calendarId'] = $konf->getId();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function renderNewsDetail(DfxNews $entity): array
    {
        $payload = parent::renderNewsDetail($entity);
        $payload['apiSource'] = 'custom-example';

        return $payload;
    }
}
