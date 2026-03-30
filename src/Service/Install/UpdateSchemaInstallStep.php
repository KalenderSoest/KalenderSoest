<?php

namespace App\Service\Install;

final class UpdateSchemaInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly MigrationInspectionService $migrationInspectionService,
    ) {
    }

    public function getName(): string
    {
        return 'update_schema';
    }

    public function execute(): array
    {
        $migrationState = $this->migrationInspectionService->inspect();
        if (($migrationState['latest_pending'] ?? null) !== null) {
            return [
                'redirect_route' => 'dfx_install_run_step',
                'redirect_params' => ['step' => 'review_update_migration'],
                'flash_warning' => 'Der alte Schema-Update-Pfad wurde auf den neuen Migrationsablauf umgestellt. Die erzeugte Migration wird jetzt angezeigt.',
            ];
        }

        return [
            'redirect_route' => 'dfx_install_run_step',
            'redirect_params' => ['step' => 'generate_update_migration'],
            'flash_warning' => 'Der alte Schema-Update-Pfad wurde auf den neuen Migrationsablauf umgestellt.',
        ];
    }
}
