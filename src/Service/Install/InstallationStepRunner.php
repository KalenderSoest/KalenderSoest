<?php

namespace App\Service\Install;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class InstallationStepRunner
{
    /**
     * @param iterable<InstallStepInterface> $steps
     */
    public function __construct(
        #[AutowireIterator('app.install_step')]
        private readonly iterable $steps,
    ) {
    }

    /**
     * @return array{template: string, data: array<string, mixed>}
     */
    public function run(string $stepName): array
    {
        foreach ($this->steps as $step) {
            if ($step->getName() === $stepName) {
                return $step->execute();
            }
        }

        throw new \InvalidArgumentException(sprintf('Unbekannter Install-Schritt "%s".', $stepName));
    }
}
