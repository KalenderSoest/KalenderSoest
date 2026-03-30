<?php

namespace App\Service\Install;

final class KonfToGroupInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
    ) {
    }

    public function getName(): string
    {
        return 'migrate_konf_to_group';
    }

    public function execute(): array
    {
        return [
            'template' => 'SuperAdmin/update_daba.html.twig',
            'data' => $this->installationExecutionService->migrateKonfToGroup(),
        ];
    }
}
