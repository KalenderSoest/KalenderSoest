<?php

namespace App\Service\Install;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ComposerMetadataReader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{
     *   composer_json_exists: bool,
     *   composer_lock_exists: bool,
     *   vendor_autoload_exists: bool,
     *   php_requirement: ?string,
     *   symfony_requirement: ?string,
     *   content_hash: ?string
     * }
     */
    public function read(): array
    {
        $composerJsonPath = $this->projectDir . '/composer.json';
        $composerLockPath = $this->projectDir . '/composer.lock';
        $vendorAutoloadPath = $this->projectDir . '/vendor/autoload.php';

        $composerJson = is_file($composerJsonPath) ? json_decode((string) file_get_contents($composerJsonPath), true) : null;
        $composerLock = is_file($composerLockPath) ? json_decode((string) file_get_contents($composerLockPath), true) : null;

        return [
            'composer_json_exists' => is_array($composerJson),
            'composer_lock_exists' => is_array($composerLock),
            'vendor_autoload_exists' => is_file($vendorAutoloadPath),
            'php_requirement' => $composerJson['require']['php'] ?? null,
            'symfony_requirement' => $composerJson['extra']['symfony']['require'] ?? null,
            'content_hash' => $composerLock['content-hash'] ?? null,
        ];
    }
}
