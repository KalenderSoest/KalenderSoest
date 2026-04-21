<?php

namespace App\Service\Install;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class InstallationStateService
{
    public function __construct(
        private readonly ComposerMetadataReader $composerMetadataReader,
        private readonly EnvironmentConfigReader $environmentConfigReader,
        private readonly DatabaseInspectionService $databaseInspectionService,
        private readonly InstallationExecutionService $installationExecutionService,
        private readonly MigrationInspectionService $migrationInspectionService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function detect(): array
    {
        $composer = $this->composerMetadataReader->read();
        $env = $this->environmentConfigReader->read();
        $database = $this->databaseInspectionService->inspect($env['database_url']);
        $database['schema_update_pending'] = $this->detectPendingSchemaUpdate($database);
        $migrations = $this->migrationInspectionService->inspect();
        $directories = $this->inspectDirectories();

        return [
            'composer' => $composer,
            'env' => $env,
            'database' => $database,
            'migrations' => $migrations,
            'directories' => $directories,
            'mode' => $this->determineMode($composer, $env, $database),
        ];
    }

    /**
     * @param array<string, mixed> $database
     */
    private function detectPendingSchemaUpdate(array $database): bool
    {
        if (($database['connectable'] ?? false) !== true) {
            return false;
        }

        if (($database['schema_ready'] ?? false) !== true) {
            return false;
        }

        if ((int) ($database['kunden_count'] ?? 0) === 0) {
            return false;
        }

        $checks = $database['checks'] ?? [];
        if (($checks['legacy_media']['existing_columns'] ?? []) !== []) {
            return true;
        }

        return $this->installationExecutionService->hasPendingSchemaChanges();
    }

    /**
     * @return array<int, array{path: string, exists: bool, writable: bool}>
     */
    private function inspectDirectories(): array
    {
        $paths = [
            '/config',
            '/var/cache',
            '/var/log',
            '/web/images/dfx',
            '/web/pdf/dfx',
            '/web/media/dfx',
        ];

        $result = [];
        foreach ($paths as $path) {
            $fullPath = $this->projectDir . $path;
            $result[] = [
                'path' => $path,
                'exists' => file_exists($fullPath),
                'writable' => file_exists($fullPath) && is_writable($fullPath),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $composer
     * @param array<string, mixed> $env
     * @param array<string, mixed> $database
     */
    private function determineMode(array $composer, array $env, array $database): string
    {
        if (($composer['vendor_autoload_exists'] ?? false) !== true) {
            return 'missing_vendor';
        }

        if (($env['database_url'] ?? null) === null) {
            return 'repair_config';
        }

        if (($database['connectable'] ?? false) !== true) {
            return 'repair_config';
        }

        if (($database['has_tables'] ?? false) !== true) {
            return 'fresh_install';
        }

        if (($database['schema_ready'] ?? false) === true && (int) ($database['kunden_count'] ?? 0) === 0) {
            return 'fresh_install_pending_setup';
        }

        return 'migrate_existing';
    }
}
