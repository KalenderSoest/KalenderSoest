<?php

namespace App\Service\Api;

use App\Entity\DfxKonf;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ApiPayloadRendererResolver
{
    public function __construct(
        private readonly SchemaOrgApiPayloadRenderer $defaultRenderer,
        #[Autowire(service: 'service_container')]
        private readonly ContainerInterface $container,
    ) {
    }

    public function forKonf(DfxKonf $konf): ApiPayloadRendererInterface
    {
        foreach ($this->candidateClasses($konf) as $class) {
            if (!class_exists($class) || !$this->container->has($class)) {
                continue;
            }

            $renderer = $this->container->get($class);
            if ($renderer instanceof ApiPayloadRendererInterface) {
                return $renderer;
            }
        }

        return $this->defaultRenderer;
    }

    /**
     * Reihenfolge analog zur Template-Logik:
     * 1. kundenspezifisch pro Kalender-ID
     * 2. globale Custom-Ausgabe
     *
     * Erwartete Klassennamen:
     * - `App\Service\Api\Custom\Kid{kid}\ApiPayloadRenderer`
     * - `App\Service\Api\Custom\ApiPayloadRenderer`
     *
     * @return list<class-string>
     */
    private function candidateClasses(DfxKonf $konf): array
    {
        return [
            'App\\Service\\Api\\Custom\\Kid' . $konf->getId() . '\\ApiPayloadRenderer',
            'App\\Service\\Api\\Custom\\ApiPayloadRenderer',
        ];
    }
}
