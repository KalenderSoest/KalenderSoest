<?php

namespace App\Service\Install;

use Doctrine\DBAL\DriverManager;
use Throwable;

final class LegacyPasswordAuditService
{
    public function audit(?string $databaseUrl): array
    {
        if ($databaseUrl === null || $databaseUrl === '') {
            return [
                'status' => 'DATABASE_URL fehlt.',
                'rows_scanned' => 0,
                'salt_rows' => 0,
                'formats' => [],
                'examples' => [],
            ];
        }

        try {
            $connection = DriverManager::getConnection(['url' => $databaseUrl]);
            $schemaManager = $connection->createSchemaManager();
            if (!$schemaManager->tablesExist(['pool_dfx_nfx_user'])) {
                $connection->close();
                return [
                    'status' => 'Tabelle pool_dfx_nfx_user nicht gefunden.',
                    'rows_scanned' => 0,
                    'salt_rows' => 0,
                    'formats' => [],
                    'examples' => [],
                ];
            }

            $columns = $schemaManager->listTableColumns('pool_dfx_nfx_user');
            $hasSalt = isset($columns['salt']);
            $select = $hasSalt ? 'id, username, password, salt' : 'id, username, password';
            $rows = $connection->fetchAllAssociative('SELECT ' . $select . ' FROM pool_dfx_nfx_user');

            $formats = [];
            $examples = [];
            $saltRows = 0;
            foreach ($rows as $row) {
                $password = (string) ($row['password'] ?? '');
                $format = $this->detectFormat($password);
                $formats[$format] = ($formats[$format] ?? 0) + 1;
                if (!isset($examples[$format]) && $password !== '') {
                    $examples[$format] = sprintf(
                        'ID %s / %s / %s',
                        $row['id'] ?? '?',
                        $row['username'] ?? 'ohne-username',
                        substr($password, 0, 20) . (strlen($password) > 20 ? '...' : '')
                    );
                }
                if ($hasSalt && !empty($row['salt'])) {
                    $saltRows++;
                }
            }

            $connection->close();

            return [
                'status' => $saltRows > 0 || array_diff(array_keys($formats), ['bcrypt', 'argon2id', 'argon2i', 'modern_unknown']) !== []
                    ? 'Alte oder gemischte Passwortsituation erkannt.'
                    : 'Passwortspeicher wirkt modern und kompatibel.',
                'rows_scanned' => count($rows),
                'salt_rows' => $saltRows,
                'formats' => $formats,
                'examples' => $examples,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'Fehler bei der Passwortpruefung: ' . $e->getMessage(),
                'rows_scanned' => 0,
                'salt_rows' => 0,
                'formats' => [],
                'examples' => [],
            ];
        }
    }

    private function detectFormat(string $password): string
    {
        if ($password === '') {
            return 'leer';
        }
        if (str_starts_with($password, '$2y$') || str_starts_with($password, '$2a$') || str_starts_with($password, '$2b$')) {
            return 'bcrypt';
        }
        if (str_starts_with($password, '$argon2id$')) {
            return 'argon2id';
        }
        if (str_starts_with($password, '$argon2i$')) {
            return 'argon2i';
        }
        if (preg_match('/^[a-f0-9]{32}$/i', $password)) {
            return 'md5_like';
        }
        if (preg_match('/^[a-f0-9]{40}$/i', $password)) {
            return 'sha1_like';
        }
        if (preg_match('/^[a-f0-9]{64}$/i', $password)) {
            return 'sha256_like';
        }
        if (preg_match('/^[a-f0-9]{128}$/i', $password)) {
            return 'sha512_like';
        }

        return 'modern_unknown';
    }
}
