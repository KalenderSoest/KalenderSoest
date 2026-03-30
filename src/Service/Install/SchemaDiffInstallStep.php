<?php

namespace App\Service\Install;

final class SchemaDiffInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
    ) {
    }

    public function getName(): string
    {
        return 'schema_diff';
    }

    public function execute(): array
    {
        return [
            'template' => 'DfxFrontend/install3.html.twig',
            'data' => [
                'msg' => nl2br($this->installationExecutionService->runSchemaDiff()),
                'continue_route' => 'dfx_install_run_step',
                'continue_params' => ['step' => 'schema_migrate'],
                'continue_label' => 'Schema anlegen',
            ],
        ];
    }
}
