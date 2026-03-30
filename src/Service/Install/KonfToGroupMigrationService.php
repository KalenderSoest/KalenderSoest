<?php

namespace App\Service\Install;

use Doctrine\DBAL\DriverManager;
use Throwable;

final class KonfToGroupMigrationService
{
    public function migrate(?string $databaseUrl): array
    {
        if ($databaseUrl === null || $databaseUrl === '') {
            return [
                'status' => 'DATABASE_URL fehlt.',
                'records_scanned' => 0,
                'records_changed' => 0,
                'rows' => [],
            ];
        }

        try {
            $connection = DriverManager::getConnection(['url' => $databaseUrl]);
            $schemaManager = $connection->createSchemaManager();
            if (!$schemaManager->tablesExist(['pool_dfx_konf'])) {
                $connection->close();
                return [
                    'status' => 'Tabelle pool_dfx_konf nicht gefunden.',
                    'records_scanned' => 0,
                    'records_changed' => 0,
                    'rows' => [],
                ];
            }

            $columns = array_change_key_case($schemaManager->listTableColumns('pool_dfx_konf'), CASE_LOWER);
            if (!isset($columns['togroup'])) {
                $connection->close();
                return [
                    'status' => 'Spalte toGroup nicht gefunden.',
                    'records_scanned' => 0,
                    'records_changed' => 0,
                    'rows' => [],
                ];
            }

            $schemaChanged = $this->ensureToGroupStorage($connection);
            $rows = $connection->fetchAllAssociative("SELECT id, toGroup FROM pool_dfx_konf WHERE toGroup IS NOT NULL AND toGroup <> ''");
            $changed = 0;
            $details = [];

            foreach ($rows as $row) {
                $normalized = $this->normalizeToGroup($row['toGroup']);
                if ($normalized === $row['toGroup']) {
                    continue;
                }

                $connection->update('pool_dfx_konf', ['toGroup' => $normalized], ['id' => $row['id']]);
                $changed++;
                $details[] = sprintf(
                    'ID %s: %s -> %s',
                    $row['id'],
                    (string) $row['toGroup'],
                    $normalized === null ? 'NULL' : $normalized
                );
            }

            $connection->close();

            return [
                'status' => $schemaChanged || $changed > 0
                    ? 'toGroup erfolgreich auf textbasierten JSON-Speicher und JSON-Inhalte umgestellt.'
                    : 'Kein toGroup-Migrationsbedarf erkannt.',
                'records_scanned' => count($rows),
                'records_changed' => $changed,
                'rows' => $details,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'Fehler bei der toGroup-Migration: ' . $e->getMessage(),
                'records_scanned' => 0,
                'records_changed' => 0,
                'rows' => [],
            ];
        }
    }

    private function ensureToGroupStorage(object $connection): bool
    {
        $column = $connection->fetchAssociative(
            "SELECT DATA_TYPE, COLUMN_TYPE, COLUMN_COMMENT
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'pool_dfx_konf'
               AND COLUMN_NAME = 'toGroup'"
        );

        if (!is_array($column)) {
            return false;
        }

        $dataType = strtolower((string) ($column['DATA_TYPE'] ?? ''));
        $columnComment = (string) ($column['COLUMN_COMMENT'] ?? '');
        $storageCompatible = in_array($dataType, ['text', 'mediumtext', 'longtext', 'json'], true)
            && str_contains($columnComment, 'DC2Type:json');

        if ($storageCompatible) {
            return false;
        }

        $connection->executeStatement("ALTER TABLE pool_dfx_konf MODIFY `toGroup` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)'");

        return true;
    }

    private function normalizeToGroup(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '' || $stringValue === '0') {
            return null;
        }

        $unserialized = @unserialize($stringValue);
        if (is_array($unserialized)) {
            return json_encode(array_values($unserialized), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
        }

        $json = json_decode($stringValue, true);
        if (is_array($json)) {
            return json_encode(array_values($json), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
        }

        if (str_contains($stringValue, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $stringValue)), static fn (string $part): bool => $part !== ''));
            return $parts === [] ? null : (json_encode($parts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null);
        }

        return json_encode([$stringValue], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }
}
