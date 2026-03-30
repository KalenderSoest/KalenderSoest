<?php

namespace App\Service\Install;

final class GenerateUpdateMigrationInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationStateService $installationStateService,
        private readonly InstallationExecutionService $installationExecutionService,
        private readonly MigrationInspectionService $migrationInspectionService,
    ) {
    }

    public function getName(): string
    {
        return 'generate_update_migration';
    }

    public function execute(): array
    {
        $state = $this->installationStateService->detect();
        $checks = $state['database']['checks'] ?? [];
        if (($checks['array_json']['needed'] ?? false) === true || ($checks['to_group']['needed'] ?? false) === true || ($checks['legacy_media']['needed'] ?? false) === true) {
            return [
                'template' => 'SuperAdmin/update_daba.html.twig',
                'data' => [
                    'msg_entities' => '',
                    'msg_update' => 'Bitte zuerst die vorgeschalteten JSON-/Legacy-Migrationen ausfuehren, bevor eine Update-Migration erzeugt wird.',
                ],
            ];
        }

        $migrationState = $this->migrationInspectionService->inspect();
        if ($migrationState['latest_pending'] !== null) {
            return [
                'redirect_route' => 'dfx_install_run_step',
                'redirect_params' => ['step' => 'review_update_migration'],
                'flash_warning' => 'Es existiert bereits eine noch nicht ausgefuehrte Update-Migration. Diese wird jetzt angezeigt.',
            ];
        }

        if (($migrationState['schema_diff_pending'] ?? false) !== true) {
            return [
                'redirect_route' => 'dfx_install_status',
                'flash_success' => 'Die Datenbank ist bereits auf dem aktuellen Stand.',
            ];
        }

        $result = $this->installationExecutionService->generateMigrationDiff();
        if (($result['file'] ?? null) === null) {
            return [
                'template' => 'SuperAdmin/update_daba.html.twig',
                'data' => [
                    'msg_entities' => '',
                    'msg_update' => nl2br(trim($result['output']) !== '' ? $result['output'] : 'Es konnte keine Update-Migration erzeugt werden.'),
                ],
            ];
        }

        return [
            'redirect_route' => 'dfx_install_run_step',
            'redirect_params' => ['step' => 'review_update_migration'],
            'flash_success' => 'Die Update-Migration wurde erzeugt und kann jetzt geprueft werden.',
        ];
    }
}
