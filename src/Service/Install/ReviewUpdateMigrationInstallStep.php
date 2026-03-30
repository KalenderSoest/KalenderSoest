<?php

namespace App\Service\Install;

final class ReviewUpdateMigrationInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly MigrationInspectionService $migrationInspectionService,
    ) {
    }

    public function getName(): string
    {
        return 'review_update_migration';
    }

    public function execute(): array
    {
        $migrationState = $this->migrationInspectionService->inspect();
        $migration = $migrationState['latest_pending'] ?? null;
        if ($migration === null) {
            return [
                'redirect_route' => 'dfx_install_status',
                'flash_warning' => 'Es liegt aktuell keine noch nicht ausgefuehrte Update-Migration zur Pruefung vor.',
            ];
        }

        return [
            'template' => 'SuperAdmin/update_migration_review.html.twig',
            'data' => [
                'migration' => $migration,
                'pending_count' => count($migrationState['pending_files'] ?? []),
            ],
        ];
    }
}
