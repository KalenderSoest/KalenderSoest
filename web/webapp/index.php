<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

$projectDir = dirname(__DIR__, 2);
$installUrl = (static function (): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = rtrim(dirname((string) ($_SERVER['PHP_SELF'] ?? '')), '/\\');
    return 'http://' . $host . preg_replace('#/webapp$#', '', $uri) . '/install/index.php';
})();

$hasVendor = is_file($projectDir . '/vendor/autoload.php');
$hasEnv = is_file($projectDir . '/.env') || is_file($projectDir . '/.env.local');
$hasDatefixConfig = is_file($projectDir . '/config/datefix.yaml');
$hasDatabaseUrl = false;

if ($hasEnv) {
    foreach ([$projectDir . '/.env', $projectDir . '/.env.local'] as $envFile) {
        if (!is_file($envFile)) {
            continue;
        }

        $content = (string) file_get_contents($envFile);
        if (preg_match('/^\s*DATABASE_URL\s*=\s*(.+)\s*$/m', $content)) {
            $hasDatabaseUrl = true;
            break;
        }
    }
}

if (!$hasVendor || !$hasEnv || !$hasDatabaseUrl || !$hasDatefixConfig) {
    header('Location: ' . $installUrl);
    exit;
}

require $projectDir . '/vendor/autoload.php';
(new Dotenv())->bootEnv($projectDir . '/.env');

if (!empty($_SERVER['APP_DEBUG'])) {
    umask(0000);
    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(
        explode(',', (string) $trustedProxies),
        Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX
    );
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

define('STDIN', fopen('php://stdin', 'r'));

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
