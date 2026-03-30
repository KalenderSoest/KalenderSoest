<?php

namespace App\Service\Install;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.install_step')]
interface InstallStepInterface
{
    public function getName(): string;

    /**
     * @return array{template: string, data: array<string, mixed>}
     */
    public function execute(): array;
}
