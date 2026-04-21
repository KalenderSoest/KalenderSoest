<?php

namespace App\Service\Install;

use App\Service\Calendar\TerminLegacyMediaMigrationService;
use App\Service\Support\ConsoleCommandService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

final class InstallationExecutionService
{
    public function __construct(
        private readonly ConsoleCommandService $consoleCommandService,
        private readonly TerminLegacyMediaMigrationService $terminLegacyMediaMigrationService,
        private readonly EnvironmentConfigReader $environmentConfigReader,
        private readonly KonfToGroupMigrationService $konfToGroupMigrationService,
        private readonly ArrayJsonMigrationService $arrayJsonMigrationService,
        private readonly LegacyPasswordAuditService $legacyPasswordAuditService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    public function runSchemaDiff(): string
    {
        $sql = $this->getPendingSchemaSql();
        if ($sql !== []) {
            return implode(";\n", $sql) . ';';
        }

        return $this->runSchemaDiffCommand();
    }

    public function hasPendingSchemaChanges(): bool
    {
        $sql = $this->getPendingSchemaSql();
        if ($sql !== []) {
            return true;
        }

        return trim($this->runSchemaDiffCommand()) !== '';
    }

    /**
     * @return list<string>
     */
    public function getPendingSchemaSql(): array
    {
        if ($this->managerRegistry === null) {
            return [];
        }

        try {
            $entityManager = $this->managerRegistry->getManager();
            if (!$entityManager instanceof EntityManagerInterface) {
                return [];
            }

            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            if ($metadata === []) {
                return [];
            }

            $sql = (new SchemaTool($entityManager))->getUpdateSchemaSql($metadata);

            return array_values(array_filter(
                array_map(static fn (mixed $statement): string => trim((string) $statement), $sql),
                static fn (string $statement): bool => $statement !== ''
            ));
        } catch (Throwable) {
            return [];
        }
    }

    private function runSchemaDiffCommand(): string
    {
        return $this->runConsoleCommand([
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--no-interaction' => true,
        ]);
    }

    public function runSchemaMigrate(): string
    {
        return $this->runConsoleCommand([
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }

    public function runUpdateMigrate(): string
    {
        return $this->runConsoleCommand([
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }

    /**
     * @return array{output:string,file:?string,version:?string,contents:string}
     */
    public function generateMigrationDiff(): array
    {
        $before = glob($this->projectDir . '/migrations/Version*.php') ?: [];
        $output = $this->runConsoleCommand([
            'command' => 'doctrine:migrations:diff',
            '--no-interaction' => true,
        ]);
        $after = glob($this->projectDir . '/migrations/Version*.php') ?: [];
        $newFiles = array_values(array_diff($after, $before));
        sort($newFiles);
        $file = $newFiles !== [] ? $newFiles[array_key_last($newFiles)] : null;

        return [
            'output' => $output,
            'file' => $file,
            'version' => $file !== null ? pathinfo($file, PATHINFO_FILENAME) : null,
            'contents' => $file !== null ? (string) @file_get_contents($file) : '',
        ];
    }

    public function migratePendingMigrations(): string
    {
        return $this->runConsoleCommand([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]);
    }

    public function clearProdCache(): string
    {
        return $this->runConsoleCommand(['command' => 'cache:clear', '--env' => 'prod']);
    }

    /**
     * @param array<string, bool|string> $input
     */
    private function runConsoleCommand(array $input): string
    {
        $env = $this->environmentConfigReader->read();
        $overrides = array_filter([
            'APP_ENV' => $env['app_env'] ?? null,
            'APP_SECRET' => $env['app_secret'] ?? null,
            'DATABASE_URL' => $env['database_url'] ?? null,
            'MAILER_DSN' => $env['mailer_dsn'] ?? null,
        ], static fn (mixed $value): bool => is_string($value) && $value !== '');

        return $this->withEnvironmentOverrides(
            $overrides,
            fn (): string => $this->consoleCommandService->run($input)
        );
    }

    /**
     * @param array<string, string> $overrides
     * @param callable():string $callback
     */
    private function withEnvironmentOverrides(array $overrides, callable $callback): string
    {
        $previous = [];

        foreach ($overrides as $key => $value) {
            $previous[$key] = [
                'server' => $_SERVER[$key] ?? null,
                'env' => $_ENV[$key] ?? null,
                'putenv' => getenv($key) === false ? null : (string) getenv($key),
            ];
            $_SERVER[$key] = $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }

        try {
            return $callback();
        } finally {
            foreach ($overrides as $key => $_value) {
                $snapshot = $previous[$key] ?? ['server' => null, 'env' => null, 'putenv' => null];

                if ($snapshot['server'] === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = (string) $snapshot['server'];
                }

                if ($snapshot['env'] === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = (string) $snapshot['env'];
                }

                if ($snapshot['putenv'] === null) {
                    putenv($key);
                } else {
                    putenv($key . '=' . $snapshot['putenv']);
                }
            }
        }
    }

    /**
     * @return array{msg_entities: string, msg_update: string}
     */
    public function migrateTerminLegacyMedia(): array
    {
        $result = $this->terminLegacyMediaMigrationService->migrate();

        $lines = [
            $result['status'],
            'Gepruefte Datensaetze: ' . $result['records_scanned'],
            'Geaenderte Datensaetze: ' . $result['records_changed'],
            'Kopierte Feldwerte: ' . $result['fields_copied'],
            'Geleerte Legacy-Felder: ' . $result['legacy_cleared'],
            'Konflikte: ' . $result['conflicts'],
        ];

        if ($result['conflicts'] > 0) {
            $lines[] = '';
            $lines[] = 'Konflikte wurden erkannt. Der Wert im Primaerfeld wurde beibehalten, der Legacy-Wert entfernt:';
            foreach ($result['conflict_rows'] as $row) {
                $lines[] = $row;
            }
        }

        return [
            'msg_entities' => '',
            'msg_update' => nl2br(implode("\n", $lines)),
        ];
    }

    public function cleanupTerminLegacyMediaColumns(): string
    {
        $result = $this->terminLegacyMediaMigrationService->dropLegacyColumns();

        $lines = [
            $result['status'],
        ];

        if ($result['dropped_columns'] !== []) {
            $lines[] = 'Entfernte Spalten: ' . implode(', ', $result['dropped_columns']);
        }

        if ($result['remaining_columns'] !== []) {
            $lines[] = 'Verbleibende Spalten: ' . implode(', ', $result['remaining_columns']);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{msg_entities: string, msg_update: string}
     */
    public function migrateKonfToGroup(): array
    {
        $env = $this->environmentConfigReader->read();
        $result = $this->konfToGroupMigrationService->migrate($env['database_url'] ?? null);

        $lines = [
            $result['status'],
            'Gepruefte Datensaetze: ' . $result['records_scanned'],
            'Geaenderte Datensaetze: ' . $result['records_changed'],
        ];

        if ($result['rows'] !== []) {
            $lines[] = '';
            foreach ($result['rows'] as $row) {
                $lines[] = $row;
            }
        }

        return [
            'msg_entities' => '',
            'msg_update' => nl2br(implode("\n", $lines)),
        ];
    }

    /**
     * @return array{msg_entities: string, msg_update: string}
     */
    public function migrateArrayJsonFields(): array
    {
        $env = $this->environmentConfigReader->read();
        $result = $this->arrayJsonMigrationService->migrate($env['database_url'] ?? null);

        $lines = [
            $result['status'],
            'Gepruefte Datensaetze: ' . $result['records_scanned'],
            'Geaenderte Datensaetze: ' . $result['records_changed'],
            'Geaenderte Felder: ' . $result['fields_changed'],
        ];

        if ($result['rows'] !== []) {
            $lines[] = '';
            foreach ($result['rows'] as $row) {
                $lines[] = $row;
            }
        }

        return [
            'msg_entities' => '',
            'msg_update' => nl2br(implode("\n", $lines)),
        ];
    }

    /**
     * @return array{msg_entities: string, msg_update: string}
     */
    public function auditLegacyPasswords(): array
    {
        $env = $this->environmentConfigReader->read();
        $result = $this->legacyPasswordAuditService->audit($env['database_url'] ?? null);

        $lines = [
            $result['status'],
            'Gepruefte Benutzer: ' . $result['rows_scanned'],
            'Benutzer mit salt: ' . $result['salt_rows'],
        ];

        if ($result['formats'] !== []) {
            $lines[] = '';
            $lines[] = 'Hashformate:';
            foreach ($result['formats'] as $format => $count) {
                $lines[] = sprintf('- %s: %d', $format, $count);
            }
        }

        if ($result['examples'] !== []) {
            $lines[] = '';
            $lines[] = 'Beispiele:';
            foreach ($result['examples'] as $format => $example) {
                $lines[] = sprintf('- %s: %s', $format, $example);
            }
        }

        return [
            'msg_entities' => '',
            'msg_update' => nl2br(implode("\n", $lines)),
        ];
    }
}
