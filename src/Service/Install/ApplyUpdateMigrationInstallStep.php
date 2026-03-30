<?php

namespace App\Service\Install;

final class ApplyUpdateMigrationInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
        private readonly InstallationStateService $installationStateService,
        private readonly MigrationInspectionService $migrationInspectionService,
    ) {
    }

    public function getName(): string
    {
        return 'apply_update_migration';
    }

    public function execute(): array
    {
        $migrationState = $this->migrationInspectionService->inspect();
        if (($migrationState['latest_pending'] ?? null) === null) {
            return [
                'redirect_route' => 'dfx_install_status',
                'flash_warning' => 'Es liegt keine ausfuehrbare Update-Migration vor.',
            ];
        }

        $output = trim($this->installationExecutionService->migratePendingMigrations());
        if ($output !== '' && (str_contains($output, '[ERROR]') || str_contains($output, 'Fehler '))) {
            return [
                'template' => 'SuperAdmin/update_daba.html.twig',
                'data' => [
                    'msg_entities' => '',
                    'msg_update' => nl2br($output),
                ],
            ];
        }

        $stateAfter = $this->installationStateService->detect();
        $migrationStateAfter = $this->migrationInspectionService->inspect();
        if (($migrationStateAfter['latest_pending'] ?? null) !== null || ($stateAfter['database']['schema_update_pending'] ?? false) === true) {
            return [
                'template' => 'SuperAdmin/update_daba.html.twig',
                'data' => [
                    'msg_entities' => '',
                    'msg_update' => nl2br(
                        trim($output) !== '' ? $output : 'Die Migration wurde ausgefuehrt, aber es sind weiterhin offene Schema-Aenderungen vorhanden.'
                    ),
                ],
            ];
        }

        return [
            'redirect_route' => 'dfx_install_status',
            'flash_success' => 'Die Update-Migration wurde erfolgreich ausgefuehrt.',
        ];
    }
}
