<?php

namespace App\Service\Install;

final class ArrayJsonInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
    ) {
    }

    public function getName(): string
    {
        return 'migrate_array_json';
    }

    public function execute(): array
    {
        return [
            'template' => 'SuperAdmin/update_daba.html.twig',
            'data' => $this->installationExecutionService->migrateArrayJsonFields(),
        ];
    }
}
