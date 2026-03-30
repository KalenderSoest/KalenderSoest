<?php

namespace App\Service\Install;

final class SchemaMigrateInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
        private readonly InstallationStateService $installationStateService,
    ) {
    }

    public function getName(): string
    {
        return 'schema_migrate';
    }

    public function execute(): array
    {
        $message = trim($this->installationExecutionService->runSchemaMigrate());
        $state = $this->installationStateService->detect();

        if (($state['database']['schema_ready'] ?? false) !== true) {
            return [
                'template' => 'DfxFrontend/install3.html.twig',
                'data' => [
                    'msg' => nl2br($message !== '' ? $message : 'Fehler beim Anlegen des Datenbankschemas.'),
                ],
            ];
        }

        return [
            'redirect_route' => 'dfx_install_status',
            'flash_success' => 'Das Datenbankschema wurde erfolgreich angelegt.',
        ];
    }
}
