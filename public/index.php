<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
if(file_exists('../config/services.yaml')){
    require dirname(__DIR__).'/vendor/autoload.php';
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
    if ($_SERVER['APP_DEBUG']) {
        umask(0000);
        Debug::enable();
    }

    if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
        Request::setTrustedProxies(explode(',', (string) $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
    }

    if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
        Request::setTrustedHosts([$trustedHosts]);
    }

    define('STDIN',fopen("php://stdin","r"));
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}else{
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname((string) $_SERVER['PHP_SELF']), '/\\');
    $ziel = 'install/index.php';
    header("Location: http://$host$uri/$ziel");
    exit;
}
