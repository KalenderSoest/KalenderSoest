<?php

namespace App\Service\Api;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;

interface ApiPayloadRendererInterface
{
    /**
     * @param list<DfxTermine> $entities
     *
     * @return list<array<string, mixed>>
     */
    public function renderTerminList(array $entities, DfxKonf $konf): array;

    /**
     * @return array<string, mixed>
     */
    public function renderTerminDetail(DfxTermine $entity): array;

    /**
     * @param list<DfxNews> $entities
     *
     * @return list<array<string, mixed>>
     */
    public function renderNewsList(array $entities, DfxKonf $konf): array;

    /**
     * @return array<string, mixed>
     */
    public function renderNewsDetail(DfxNews $entity): array;
}
