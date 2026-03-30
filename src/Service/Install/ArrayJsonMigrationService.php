<?php

namespace App\Service\Install;

use Doctrine\DBAL\DriverManager;
use Throwable;

final class ArrayJsonMigrationService
{
    /**
     * @var list<array{table:string,column:string,label:string}>
     */
    private const FIELDS = [
        ['table' => 'pool_dfx_konf', 'column' => 'rubriken', 'label' => 'DfxKonf.rubriken'],
        ['table' => 'pool_dfx_konf', 'column' => 'zielgruppen', 'label' => 'DfxKonf.zielgruppen'],
        ['table' => 'pool_dfx_konf', 'column' => 'toGroup', 'label' => 'DfxKonf.toGroup'],
        ['table' => 'pool_dfx_news', 'column' => 'rubrik', 'label' => 'DfxNews.rubrik'],
        ['table' => 'pool_dfx_termine', 'column' => 'rubrik', 'label' => 'DfxTermine.rubrik'],
        ['table' => 'pool_dfx_termine', 'column' => 'zielgruppe', 'label' => 'DfxTermine.zielgruppe'],
        ['table' => 'pool_dfx_termine', 'column' => 'karten_kat', 'label' => 'DfxTermine.karten_kat'],
        ['table' => 'pool_dfx_termine', 'column' => 'options_checkboxes', 'label' => 'DfxTermine.options_checkboxes'],
        ['table' => 'pool_dfx_termine', 'column' => 'options_menue_multi', 'label' => 'DfxTermine.options_menue_multi'],
        ['table' => 'pool_dfx_nfx_user', 'column' => 'roles', 'label' => 'DfxNfxUser.roles'],
    ];

    /**
     * @return array{status:string,records_scanned:int,records_changed:int,fields_changed:int,rows:list<string>}
     */
    public function migrate(?string $databaseUrl): array
    {
        if ($databaseUrl === null || $databaseUrl === '') {
            return [
                'status' => 'DATABASE_URL fehlt.',
                'records_scanned' => 0,
                'records_changed' => 0,
                'fields_changed' => 0,
                'rows' => [],
            ];
        }

        try {
            $connection = DriverManager::getConnection(['url' => $databaseUrl]);
            $schemaManager = $connection->createSchemaManager();
            $changedRows = [];
            $recordsScanned = 0;
            $recordsChanged = 0;
            $fieldsChanged = 0;
            $storageChanged = false;

            foreach (self::FIELDS as $field) {
                if (!$schemaManager->tablesExist([$field['table']])) {
                    continue;
                }

                $columns = array_change_key_case($schemaManager->listTableColumns($field['table']), CASE_LOWER);
                if (!isset($columns[strtolower($field['column'])])) {
                    continue;
                }

                $storageChanged = $this->ensureJsonStorage($connection, $field['table'], $field['column']) || $storageChanged;
                $rows = $connection->fetchAllAssociative(
                    sprintf(
                        'SELECT id, `%1$s` AS field_value FROM `%2$s` WHERE `%1$s` IS NOT NULL AND `%1$s` <> \'\'',
                        $field['column'],
                        $field['table']
                    )
                );
                $recordsScanned += count($rows);

                foreach ($rows as $row) {
                    $normalized = $this->normalizeToJson($row['field_value']);
                    if ($normalized === $row['field_value']) {
                        continue;
                    }

                    $connection->update(
                        $field['table'],
                        [$field['column'] => $normalized],
                        ['id' => $row['id']]
                    );
                    $recordsChanged++;
                    $fieldsChanged++;
                    $changedRows[] = sprintf(
                        '%s ID %s: %s -> %s',
                        $field['label'],
                        $row['id'],
                        (string) $row['field_value'],
                        $normalized === null ? 'NULL' : $normalized
                    );
                }
            }

            $connection->close();

            return [
                'status' => ($storageChanged || $fieldsChanged > 0)
                    ? 'Array-Felder erfolgreich auf JSON-Speicher und JSON-Inhalte umgestellt.'
                    : 'Kein JSON-Migrationsbedarf bei den Array-Feldern erkannt.',
                'records_scanned' => $recordsScanned,
                'records_changed' => $recordsChanged,
                'fields_changed' => $fieldsChanged,
                'rows' => $changedRows,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'Fehler bei der JSON-Migration der Array-Felder: ' . $e->getMessage(),
                'records_scanned' => 0,
                'records_changed' => 0,
                'fields_changed' => 0,
                'rows' => [],
            ];
        }
    }

    private function ensureJsonStorage(object $connection, string $table, string $column): bool
    {
        $columnInfo = $connection->fetchAssociative(
            "SELECT DATA_TYPE, COLUMN_COMMENT
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :column",
            ['table' => $table, 'column' => $column]
        );

        if (!is_array($columnInfo)) {
            return false;
        }

        $dataType = strtolower((string) ($columnInfo['DATA_TYPE'] ?? ''));
        $columnComment = (string) ($columnInfo['COLUMN_COMMENT'] ?? '');
        $storageCompatible = in_array($dataType, ['text', 'mediumtext', 'longtext', 'json'], true)
            && str_contains($columnComment, 'DC2Type:json');

        if ($storageCompatible) {
            return false;
        }

        $connection->executeStatement(
            sprintf(
                "ALTER TABLE `%s` MODIFY `%s` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)'",
                $table,
                $column
            )
        );

        return true;
    }

    private function normalizeToJson(mixed $value): ?string
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
}
