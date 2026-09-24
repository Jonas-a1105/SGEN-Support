<?php
/**
 * Bootstrap - Inicializa Container DI y middlewares
 * Reemplaza la inicialización manual en public/index.php
 */

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Core\Router;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\RateLimitMiddleware;
use App\Core\Middleware\AuthMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// 1. Build Container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(true);
$container = $containerBuilder->build();

// 2. Make container globally accessible (opcional, para migración gradual)
$GLOBALS['container'] = $container;

// 3. Register Middleware Pipeline en Router
$router = $container->get(Router::class);
$router->addGlobalMiddleware([
    CsrfMiddleware::class,
    RateLimitMiddleware::class,
    AuthMiddleware::class,
]);

// 4. Helper para resolver controladores desde container
function controller(string $className): object
{
    global $container;
    return $container->get($className);
}

// 5. Helper para Actions (útil en scripts CLI, tests)
function action(string $className): object
{
    global $container;
    return $container->get($className);
}

return $container;