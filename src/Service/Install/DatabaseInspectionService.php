<?php

namespace App\Service\Install;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Throwable;

final class DatabaseInspectionService
{
    /**
     * @return array{
     *   configured: bool,
     *   connectable: bool,
     *   database_name: ?string,
     *   table_count: int,
     *   has_tables: bool,
     *   has_datefix_tables: bool,
     *   schema_ready: bool,
     *   kunden_count: int,
     *   user_count: int,
     *   sample_tables: array<int, string>,
     *   checks: array<string, mixed>,
     *   error: ?string
     * }
     */
    public function inspect(?string $databaseUrl): array
    {
        if ($databaseUrl === null || $databaseUrl === '') {
            return [
                'configured' => false,
                'connectable' => false,
                'database_name' => null,
                'table_count' => 0,
                'has_tables' => false,
                'has_datefix_tables' => false,
                'schema_ready' => false,
                'kunden_count' => 0,
                'user_count' => 0,
                'sample_tables' => [],
                'checks' => $this->emptyChecks(),
                'error' => 'DATABASE_URL fehlt.',
            ];
        }

        try {
            $connection = DriverManager::getConnection(['url' => $databaseUrl]);
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            $datefixTables = array_filter($tables, static fn (string $name): bool => str_contains($name, 'dfx') || str_contains($name, 'datefix'));
            $schemaReady = in_array('pool_dfx_konf', $tables, true)
                && in_array('pool_dfx_nfx_user', $tables, true)
                && in_array('pool_dfx_nfx_kunden', $tables, true);
            $kundenCount = in_array('pool_dfx_nfx_kunden', $tables, true)
                ? (int) $connection->fetchOne('SELECT COUNT(*) FROM pool_dfx_nfx_kunden')
                : 0;
            $userCount = in_array('pool_dfx_nfx_user', $tables, true)
                ? (int) $connection->fetchOne('SELECT COUNT(*) FROM pool_dfx_nfx_user')
                : 0;
            $checks = $this->inspectChecks($connection, $schemaManager, $tables);
            $connection->close();

            return [
                'configured' => true,
                'connectable' => true,
                'database_name' => $this->extractDatabaseName($databaseUrl),
                'table_count' => count($tables),
                'has_tables' => $tables !== [],
                'has_datefix_tables' => $datefixTables !== [],
                'schema_ready' => $schemaReady,
                'kunden_count' => $kundenCount,
                'user_count' => $userCount,
                'sample_tables' => array_slice($tables, 0, 10),
                'checks' => $checks,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'configured' => true,
                'connectable' => false,
                'database_name' => $this->extractDatabaseName($databaseUrl),
                'table_count' => 0,
                'has_tables' => false,
                'has_datefix_tables' => false,
                'schema_ready' => false,
                'kunden_count' => 0,
                'user_count' => 0,
                'sample_tables' => [],
                'checks' => $this->emptyChecks(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array<int, string> $tables
     * @return array<string, mixed>
     */
    private function inspectChecks(object $connection, object $schemaManager, array $tables): array
    {
        $checks = $this->emptyChecks();

        if (in_array('pool_dfx_konf', $tables, true)) {
            $konfColumns = $schemaManager->listTableColumns('pool_dfx_konf');
            $checks['to_group'] = $this->inspectToGroup($konfColumns, $connection);
            $checks['array_json'] = $this->inspectArrayJson($connection, $schemaManager, $tables);
        }

        if (in_array('pool_dfx_termine', $tables, true)) {
            $termineColumns = $schemaManager->listTableColumns('pool_dfx_termine');
            $checks['legacy_media'] = $this->inspectLegacyMedia($termineColumns, $connection);
        }

        if (in_array('pool_dfx_nfx_user', $tables, true)) {
            $userColumns = $schemaManager->listTableColumns('pool_dfx_nfx_user');
            $checks['legacy_passwords'] = $this->inspectLegacyPasswords($userColumns);
        }

        return $checks;
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string, mixed>
     */
    private function inspectToGroup(array $columns, object $connection): array
    {
        $normalizedColumns = array_change_key_case($columns, CASE_LOWER);
        if (!isset($normalizedColumns['togroup'])) {
            return [
                'needed' => false,
                'level' => 'ok',
                'message' => 'Spalte toGroup nicht vorhanden.',
                'column_type' => null,
                'non_empty_rows' => 0,
            ];
        }

        $type = $normalizedColumns['togroup']->getType()->getName();
        $columnInfo = $connection->fetchAssociative(
            "SELECT DATA_TYPE, COLUMN_TYPE, COLUMN_COMMENT
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'pool_dfx_konf'
               AND COLUMN_NAME = 'toGroup'"
        ) ?: [];
        $dataType = strtolower((string) ($columnInfo['DATA_TYPE'] ?? ''));
        $columnComment = (string) ($columnInfo['COLUMN_COMMENT'] ?? '');
        $storageCompatible = in_array($dataType, ['text', 'mediumtext', 'longtext', 'json'], true)
            && str_contains($columnComment, 'DC2Type:json');
        $rows = $connection->fetchAllAssociative("SELECT id, toGroup FROM pool_dfx_konf WHERE toGroup IS NOT NULL AND toGroup <> ''");
        $nonEmptyRows = count($rows);
        $rowsNeedingMigration = 0;

        foreach ($rows as $row) {
            if ($this->normalizeToGroupValue($row['toGroup']) !== $row['toGroup']) {
                $rowsNeedingMigration++;
            }
        }

        $needsMigration = !$storageCompatible || $rowsNeedingMigration > 0;

        return [
            'needed' => $needsMigration,
            'level' => $needsMigration ? 'critical' : 'ok',
            'message' => $needsMigration
                ? 'toGroup nutzt noch kein passendes Mehrfachwert-Storage oder enthaelt noch Altdaten.'
                : 'toGroup liegt bereits im aktuellen Mehrfachwert-Format vor.',
            'column_type' => $type,
            'storage_type' => $dataType,
            'storage_comment' => $columnComment,
            'non_empty_rows' => $nonEmptyRows,
            'rows_needing_migration' => $rowsNeedingMigration,
        ];
    }

    private function normalizeToGroupValue(mixed $value): ?string
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

    /**
     * @param array<string, Column> $columns
     * @return array<string, mixed>
     */
    private function inspectLegacyMedia(array $columns, object $connection): array
    {
        $legacyColumns = ['imgSerie', 'imgSerie2', 'imgSerie3', 'imgSerie4', 'imgSerie5', 'pdfSerie', 'mediaSerie'];
        $normalizedColumns = array_change_key_case($columns, CASE_LOWER);
        $existingColumns = array_values(array_filter($legacyColumns, static fn (string $name): bool => isset($normalizedColumns[strtolower($name)])));
        if ($existingColumns === []) {
            return [
                'needed' => false,
                'level' => 'ok',
                'message' => 'Keine Legacy-Medien-Spalten mehr vorhanden.',
                'existing_columns' => [],
                'rows_with_data' => 0,
            ];
        }

        $conditions = [];
        foreach ($existingColumns as $column) {
            $conditions[] = sprintf("%s IS NOT NULL AND %s <> ''", $column, $column);
        }
        $rowsWithData = (int) $connection->fetchOne('SELECT COUNT(*) FROM pool_dfx_termine WHERE ' . implode(' OR ', $conditions));

        return [
            'needed' => $rowsWithData > 0,
            'level' => $rowsWithData > 0 ? 'critical' : 'warning',
            'message' => $rowsWithData > 0
                ? 'Legacy-Medienfelder enthalten noch Daten und müssen übernommen werden.'
                : 'Legacy-Medien-Spalten sind noch vorhanden, aber leer.',
            'existing_columns' => $existingColumns,
            'rows_with_data' => $rowsWithData,
        ];
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string, mixed>
     */
    private function inspectLegacyPasswords(array $columns): array
    {
        $hasSalt = isset($columns['salt']);
        $hasModernLoginFields = isset($columns['username'], $columns['email'], $columns['password']);
        $hasLegacyProfileFields = isset($columns['autor']) || isset($columns['kuerzel']);
        $needsAttention = $hasSalt || !$hasModernLoginFields;

        return [
            'needed' => $needsAttention,
            'level' => $needsAttention ? 'warning' : 'ok',
            'message' => $needsAttention
                ? 'Alte Passwort-/Login-Struktur erkannt. Passwort-Reset oder Rehash-Prüfung einplanen.'
                : 'Login-Struktur entspricht dem aktuellen Stand.',
            'has_salt' => $hasSalt,
            'has_modern_login_fields' => $hasModernLoginFields,
            'has_legacy_profile_fields' => $hasLegacyProfileFields,
        ];
    }

    /**
     * @param array<int, string> $tables
     * @return array<string, mixed>
     */
    private function inspectArrayJson(object $connection, object $schemaManager, array $tables): array
    {
        $fields = [
            ['table' => 'pool_dfx_konf', 'column' => 'rubriken'],
            ['table' => 'pool_dfx_konf', 'column' => 'zielgruppen'],
            ['table' => 'pool_dfx_konf', 'column' => 'toGroup'],
            ['table' => 'pool_dfx_news', 'column' => 'rubrik'],
            ['table' => 'pool_dfx_termine', 'column' => 'rubrik'],
            ['table' => 'pool_dfx_termine', 'column' => 'zielgruppe'],
            ['table' => 'pool_dfx_termine', 'column' => 'karten_kat'],
            ['table' => 'pool_dfx_termine', 'column' => 'options_checkboxes'],
            ['table' => 'pool_dfx_termine', 'column' => 'options_menue_multi'],
            ['table' => 'pool_dfx_nfx_user', 'column' => 'roles'],
        ];

        $affected = [];
        foreach ($fields as $field) {
            if (!in_array($field['table'], $tables, true)) {
                continue;
            }

            $columns = array_change_key_case($schemaManager->listTableColumns($field['table']), CASE_LOWER);
            if (!isset($columns[strtolower($field['column'])])) {
                continue;
            }

            $columnInfo = $connection->fetchAssociative(
                "SELECT DATA_TYPE, COLUMN_COMMENT
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table
                   AND COLUMN_NAME = :column",
                ['table' => $field['table'], 'column' => $field['column']]
            ) ?: [];
            $dataType = strtolower((string) ($columnInfo['DATA_TYPE'] ?? ''));
            $columnComment = (string) ($columnInfo['COLUMN_COMMENT'] ?? '');
            $storageCompatible = in_array($dataType, ['text', 'mediumtext', 'longtext', 'json'], true)
                && str_contains($columnComment, 'DC2Type:json');
            $rows = $connection->fetchAllAssociative(
                sprintf(
                    'SELECT id, `%1$s` AS field_value FROM `%2$s` WHERE `%1$s` IS NOT NULL AND `%1$s` <> \'\'',
                    $field['column'],
                    $field['table']
                )
            );
            $rowsNeedingMigration = 0;
            foreach ($rows as $row) {
                if ($this->normalizeJsonFieldValue($row['field_value']) !== $row['field_value']) {
                    $rowsNeedingMigration++;
                }
            }

            if (!$storageCompatible || $rowsNeedingMigration > 0) {
                $affected[] = [
                    'table' => $field['table'],
                    'column' => $field['column'],
                    'rows_needing_migration' => $rowsNeedingMigration,
                    'storage_type' => $dataType,
                    'storage_comment' => $columnComment,
                ];
            }
        }

        return [
            'needed' => $affected !== [],
            'level' => $affected !== [] ? 'critical' : 'ok',
            'message' => $affected !== []
                ? 'Mehrfachwert-Felder nutzen noch alten Array-Speicher oder enthalten noch keine JSON-Inhalte.'
                : 'Mehrfachwert-Felder liegen bereits im JSON-Format vor.',
            'affected_fields' => $affected,
        ];
    }

    private function normalizeJsonFieldValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '' || $stringValue === '0') {
            return null;
        }

        $json = json_decode($stringValue, true);
        if (is_array($json)) {
            return json_encode(array_values($json), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
        }

        $unserialized = @unserialize($stringValue);
        if (is_array($unserialized)) {
            return json_encode(array_values($unserialized), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
        }

        if (str_contains($stringValue, ',')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $stringValue)), static fn (string $part): bool => $part !== ''));
            return $parts === [] ? null : (json_encode($parts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null);
        }

        return json_encode([$stringValue], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyChecks(): array
    {
        return [
            'array_json' => [
                'needed' => false,
                'level' => 'unknown',
                'message' => 'Noch nicht geprüft.',
                'affected_fields' => [],
            ],
            'to_group' => [
                'needed' => false,
                'level' => 'unknown',
                'message' => 'Noch nicht geprüft.',
            ],
            'legacy_media' => [
                'needed' => false,
                'level' => 'unknown',
                'message' => 'Noch nicht geprüft.',
            ],
            'legacy_passwords' => [
                'needed' => false,
                'level' => 'unknown',
                'message' => 'Noch nicht geprüft.',
            ],
        ];
    }

    private function extractDatabaseName(string $databaseUrl): ?string
    {
        $path = parse_url($databaseUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        return ltrim($path, '/');
    }
}
