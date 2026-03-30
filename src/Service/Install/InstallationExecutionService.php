<?php

namespace App\Service\Install;

use App\Service\Calendar\TerminLegacyMediaMigrationService;
use App\Service\Support\ConsoleCommandService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
    ) {
    }

    public function runSchemaDiff(): string
    {
        return $this->consoleCommandService->run([
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--no-interaction' => true,
        ]);
    }

    public function runSchemaMigrate(): string
    {
        return $this->consoleCommandService->run([
            'command' => 'doctrine:schema:update',
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }

    public function runUpdateMigrate(): string
    {
        return $this->consoleCommandService->run([
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
        $output = $this->consoleCommandService->run([
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
        return $this->consoleCommandService->run([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]);
    }

    public function clearProdCache(): string
    {
        return $this->consoleCommandService->run(['command' => 'cache:clear', '--env' => 'prod']);
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
