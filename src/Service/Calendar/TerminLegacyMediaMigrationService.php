<?php

namespace App\Service\Calendar;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final class TerminLegacyMediaMigrationService
{
    private const TABLE = 'pool_dfx_termine';

    private const FIELD_MAP = [
        ['primary' => 'img', 'legacy' => 'imgSerie', 'label' => 'img'],
        ['primary' => 'img2', 'legacy' => 'imgSerie2', 'label' => 'img2'],
        ['primary' => 'img3', 'legacy' => 'imgSerie3', 'label' => 'img3'],
        ['primary' => 'img4', 'legacy' => 'imgSerie4', 'label' => 'img4'],
        ['primary' => 'img5', 'legacy' => 'imgSerie5', 'label' => 'img5'],
        ['primary' => 'pdf', 'legacy' => 'pdfSerie', 'label' => 'pdf'],
        ['primary' => 'media', 'legacy' => 'mediaSerie', 'label' => 'media'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array{records_scanned:int,records_changed:int,fields_copied:int,legacy_cleared:int,conflicts:int,conflict_rows:list<string>,status:string}
     */
    public function migrate(): array
    {
        $connection = $this->em->getConnection();
        $columns = $this->getExistingColumns($connection);
        $normalizedColumns = array_change_key_case(array_flip($columns), CASE_LOWER);
        $availableFieldMap = array_values(array_filter(
            self::FIELD_MAP,
            static fn (array $config): bool => isset($normalizedColumns[strtolower($config['legacy'])])
        ));
        $legacyColumns = array_map(static fn (array $config): string => $config['legacy'], $availableFieldMap);

        if ($legacyColumns === []) {
            return [
                'records_scanned' => 0,
                'records_changed' => 0,
                'fields_copied' => 0,
                'legacy_cleared' => 0,
                'conflicts' => 0,
                'conflict_rows' => [],
                'status' => 'Legacy-Spalten bereits entfernt oder nicht vorhanden.',
            ];
        }

        $selectColumns = ['id'];
        $whereConditions = [];
        foreach ($availableFieldMap as $config) {
            $selectColumns[] = $config['primary'];
            $selectColumns[] = $config['legacy'];
            $whereConditions[] = $config['legacy'] . ' IS NOT NULL';
        }

        $rows = $connection->fetchAllAssociative(
            'SELECT ' . implode(', ', array_unique($selectColumns)) . '
             FROM ' . self::TABLE . '
             WHERE ' . implode(' OR ', $whereConditions) . '
             ORDER BY id ASC'
        );

        $stats = [
            'records_scanned' => count($rows),
            'records_changed' => 0,
            'fields_copied' => 0,
            'legacy_cleared' => 0,
            'conflicts' => 0,
            'conflict_rows' => [],
            'status' => 'Legacy-Medienfelder geprüft.',
        ];

        foreach ($rows as $row) {
            $updateData = [];

            foreach ($availableFieldMap as $config) {
                $primary = $this->normalizeDbValue($row[$config['primary']] ?? null);
                $legacy = $this->normalizeDbValue($row[$config['legacy']] ?? null);

                if ($legacy === null) {
                    continue;
                }

                if ($primary === null) {
                    $updateData[$config['primary']] = $legacy;
                    $updateData[$config['legacy']] = null;
                    $stats['fields_copied']++;
                    $stats['legacy_cleared']++;
                    continue;
                }

                if ($primary === $legacy) {
                    $updateData[$config['legacy']] = null;
                    $stats['legacy_cleared']++;
                    continue;
                }

                $updateData[$config['legacy']] = null;
                $stats['legacy_cleared']++;
                $stats['conflicts']++;
                $stats['conflict_rows'][] = sprintf(
                    'Termin %d Feld %s: primary="%s" gewinnt, legacy="%s" wird verworfen',
                    (int) $row['id'],
                    $config['label'],
                    $primary,
                    $legacy
                );
            }

            if ($updateData === []) {
                continue;
            }

            $connection->update(
                self::TABLE,
                $updateData,
                ['id' => (int) $row['id']],
                $this->buildParameterTypes($updateData)
            );
            $stats['records_changed']++;
        }

        return $stats;
    }

    /**
     * @return array{dropped_columns:list<string>,remaining_columns:list<string>,status:string}
     */
    public function dropLegacyColumns(): array
    {
        $connection = $this->em->getConnection();
        $legacyColumns = $this->getExistingLegacyColumns($connection);

        if ($legacyColumns === []) {
            return [
                'dropped_columns' => [],
                'remaining_columns' => [],
                'status' => 'Keine Legacy-Spalten mehr vorhanden.',
            ];
        }

        $dropParts = array_map(
            static fn (string $column): string => sprintf('DROP COLUMN `%s`', $column),
            $legacyColumns
        );
        $connection->executeStatement(
            'ALTER TABLE ' . self::TABLE . ' ' . implode(', ', $dropParts)
        );

        $remainingColumns = $this->getExistingLegacyColumns($connection);

        return [
            'dropped_columns' => $legacyColumns,
            'remaining_columns' => $remainingColumns,
            'status' => $remainingColumns === []
                ? 'Legacy-Spalten wurden entfernt.'
                : 'Ein Teil der Legacy-Spalten ist weiterhin vorhanden.',
        ];
    }

    /**
     * @return list<string>
     */
    private function getExistingColumns(Connection $connection): array
    {
        $columns = $connection->createSchemaManager()->listTableColumns(self::TABLE);

        return array_keys(array_change_key_case($columns, CASE_LOWER));
    }

    /**
     * @return list<string>
     */
    private function getExistingLegacyColumns(Connection $connection): array
    {
        $columns = $this->getExistingColumns($connection);
        $normalizedColumns = array_change_key_case(array_flip($columns), CASE_LOWER);

        return array_values(array_filter(
            array_map(static fn (array $config): string => $config['legacy'], self::FIELD_MAP),
            static fn (string $column): bool => isset($normalizedColumns[strtolower($column)])
        ));
    }

    private function normalizeDbValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string,mixed> $updateData
     * @return array<string,int>
     */
    private function buildParameterTypes(array $updateData): array
    {
        $types = [];
        foreach ($updateData as $column => $value) {
            $types[$column] = $value === null ? ParameterType::NULL : ParameterType::STRING;
        }

        return $types;
    }
}
