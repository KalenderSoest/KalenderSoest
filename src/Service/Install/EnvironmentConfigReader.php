<?php

namespace App\Service\Install;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class EnvironmentConfigReader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{
     *   files: array<int, string>,
     *   values: array<string, string>,
     *   database_url: ?string,
     *   mailer_dsn: ?string,
     *   app_env: ?string,
     *   app_secret: ?string
     * }
     */
    public function read(): array
    {
        $files = [];
        $values = [];

        foreach ([$this->projectDir . '/.env', $this->projectDir . '/.env.local'] as $file) {
            if (!is_file($file)) {
                continue;
            }

            $files[] = $file;
            $values = array_merge($values, $this->parseEnvFile($file));
        }

        return [
            'files' => $files,
            'values' => $values,
            'database_url' => $values['DATABASE_URL'] ?? null,
            'mailer_dsn' => $values['MAILER_DSN'] ?? null,
            'app_env' => $values['APP_ENV'] ?? null,
            'app_secret' => $values['APP_SECRET'] ?? null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseEnvFile(string $path): array
    {
        $values = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (str_starts_with($trimmed, 'export ')) {
                $trimmed = substr($trimmed, 7);
            }

            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $values[$key] = trim($value, "\"'");
        }

        return $values;
    }
}
