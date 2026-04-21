<?php

namespace App\Service\Install;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

final class MigrationInspectionService
{
    public function __construct(
        private readonly EnvironmentConfigReader $environmentConfigReader,
        private readonly InstallationExecutionService $installationExecutionService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{
     *   files:list<array{version:string,path:string,executed:bool}>,
     *   pending_files:list<array{version:string,path:string,executed:bool}>,
     *   latest_pending:?array{version:string,path:string,executed:bool,contents:string},
     *   schema_diff_pending:bool
     * }
     */
    public function inspect(): array
    {
        $files = $this->getMigrationFiles();
        $executedVersions = $this->getExecutedVersions();
        $annotatedFiles = [];

        foreach ($files as $file) {
            $annotatedFiles[] = [
                'version' => $file['version'],
                'path' => $file['path'],
                'executed' => in_array($file['version'], $executedVersions, true)
                    || in_array('DoctrineMigrations\\' . $file['version'], $executedVersions, true),
            ];
        }

        $pendingFiles = array_values(array_filter(
            $annotatedFiles,
            static fn (array $file): bool => $file['executed'] === false
        ));
        usort($pendingFiles, static fn (array $a, array $b): int => strcmp($a['version'], $b['version']));

        $latestPending = null;
        if ($pendingFiles !== []) {
            $file = $pendingFiles[array_key_last($pendingFiles)];
            $latestPending = $file + ['contents' => (string) @file_get_contents($file['path'])];
        }

        return [
            'files' => $annotatedFiles,
            'pending_files' => $pendingFiles,
            'latest_pending' => $latestPending,
            'schema_diff_pending' => $this->installationExecutionService->hasPendingSchemaChanges(),
        ];
    }

    /**
     * @return list<array{version:string,path:string}>
     */
    private function getMigrationFiles(): array
    {
        $paths = glob($this->projectDir . '/migrations/Version*.php') ?: [];
        sort($paths);

        return array_values(array_map(static function (string $path): array {
            return [
                'version' => pathinfo($path, PATHINFO_FILENAME),
                'path' => $path,
            ];
        }, $paths));
    }

    /**
     * @return list<string>
     */
    private function getExecutedVersions(): array
    {
        $env = $this->environmentConfigReader->read();
        $databaseUrl = $env['database_url'] ?? null;
        if ($databaseUrl === null || $databaseUrl === '') {
            return [];
        }

        try {
            $connection = DriverManager::getConnection(['url' => $databaseUrl]);
            $schemaManager = $connection->createSchemaManager();
            if (!in_array('doctrine_migration_versions', $schemaManager->listTableNames(), true)) {
                $connection->close();
                return [];
            }

            $versions = $connection->fetchFirstColumn('SELECT version FROM doctrine_migration_versions');
            $connection->close();

            return array_values(array_filter(array_map('strval', $versions)));
        } catch (Throwable) {
            return [];
        }
    }
}
