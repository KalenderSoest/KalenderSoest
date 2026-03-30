<?php

namespace App\Service\Support;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

final class ConsoleCommandService
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function run(array $input): string
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        if (!array_key_exists('--no-debug', $input)) {
            $input['--no-debug'] = true;
        }

        $consoleInput = new ArrayInput($input);
        $output = new BufferedOutput();
        $previousErrorReporting = error_reporting();
        $previousAppDebug = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? null;
        $previousShellVerbosity = $_SERVER['SHELL_VERBOSITY'] ?? $_ENV['SHELL_VERBOSITY'] ?? null;

        try {
            $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0';
            $_SERVER['SHELL_VERBOSITY'] = $_ENV['SHELL_VERBOSITY'] = '-1';
            error_reporting($previousErrorReporting & ~E_USER_DEPRECATED & ~E_DEPRECATED);
            $application->run($consoleInput, $output);
            return $this->sanitizeOutput($output->fetch(), (string) ($input['command'] ?? ''));
        } catch (Exception) {
            return 'Fehler beim Aufruf der Console';
        } finally {
            error_reporting($previousErrorReporting);
            if ($previousAppDebug === null) {
                unset($_SERVER['APP_DEBUG'], $_ENV['APP_DEBUG']);
            } else {
                $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (string) $previousAppDebug;
            }

            if ($previousShellVerbosity === null) {
                unset($_SERVER['SHELL_VERBOSITY'], $_ENV['SHELL_VERBOSITY']);
            } else {
                $_SERVER['SHELL_VERBOSITY'] = $_ENV['SHELL_VERBOSITY'] = (string) $previousShellVerbosity;
            }
        }
    }

    private function sanitizeOutput(string $output, string $command): string
    {
        if ($command === 'doctrine:schema:update') {
            return $this->sanitizeSchemaUpdateOutput($output);
        }

        $lines = preg_split("/\r\n|\n|\r/", $output) ?: [];
        $filtered = array_filter($lines, static function (string $line): bool {
            return !str_contains($line, 'User Deprecated:')
                && !str_contains($line, '[php] User Deprecated:')
                && !str_contains($line, 'doctrine/deprecations')
                && !str_contains($line, 'exception" => ErrorException');
        });

        return trim(implode("\n", $filtered));
    }

    private function sanitizeSchemaUpdateOutput(string $output): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $output);

        if (preg_match('/Updating database schema\.\.\.(.*?)\[OK\].*$/s', $normalized, $matches) === 1) {
            $body = trim($matches[1]);
            $lines = ['Aktualisiere Datenbank'];
            if ($body !== '') {
                $lines[] = $body;
            }
            $lines[] = '[OK] Database schema updated successfully!';

            return implode("\n\n", $lines);
        }

        if (preg_match('/^\s*\[OK\].*$/m', $normalized, $matches) === 1) {
            return trim($matches[0]);
        }

        $lines = preg_split("/\n/", $normalized) ?: [];
        $filtered = array_filter($lines, static function (string $line): bool {
            return !str_contains($line, 'User Deprecated:')
                && !str_contains($line, '[php] User Deprecated:')
                && !str_contains($line, 'doctrine/deprecations')
                && !preg_match('/^[\[\]{}(),"# ]*$/', $line)
                && !str_contains($line, 'trace:')
                && !str_contains($line, '#message:')
                && !str_contains($line, '#file:')
                && !str_contains($line, '#line:')
                && !str_contains($line, '#severity:')
                && !str_contains($line, 'ErrorException')
                && !str_contains($line, 'App\\Service\\Support\\ConsoleCommandService')
                && !str_contains($line, 'vendor\\');
        });

        return trim(implode("\n", $filtered));
    }
}
