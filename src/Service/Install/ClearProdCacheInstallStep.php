<?php

namespace App\Service\Install;

final class ClearProdCacheInstallStep implements InstallStepInterface
{
    public function __construct(
        private readonly InstallationExecutionService $installationExecutionService,
    ) {
    }

    public function getName(): string
    {
        return 'clear_prod_cache';
    }

    public function execute(): array
    {
        $output = trim($this->installationExecutionService->clearProdCache());
        if ($output !== '' && (str_contains($output, '[ERROR]') || str_contains($output, 'Fehler '))) {
            return [
                'template' => 'SuperAdmin/clearcache.html.twig',
                'data' => ['msg' => nl2br($output)],
            ];
        }

        return [
            'redirect_route' => 'dfx_install_status',
            'flash_success' => 'Der Produktivcache wurde erfolgreich geleert.',
        ];
    }
}
