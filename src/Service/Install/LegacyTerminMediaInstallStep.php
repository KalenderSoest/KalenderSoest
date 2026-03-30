<?php

namespace App\Service\Install;

final class LegacyTerminMediaInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
    ) {
    }

    public function getName(): string
    {
        return 'legacy_termin_media';
    }

    public function execute(): array
    {
        return [
            'template' => 'SuperAdmin/update_daba.html.twig',
            'data' => $this->installationExecutionService->migrateTerminLegacyMedia(),
        ];
    }
}
