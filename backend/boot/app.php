<?php

use DI\Container;
use DI\Bridge\Slim\Bridge;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteCollectorProxy;
use TcgMarket\Handler\ApiErrorHandler;

// Set up constants
require __DIR__ . '/constants.php';

// Composer autoloader
require VENDOR_PATH . 'autoload.php';

$container = new Container(require BOOT_PATH . 'config.php');
$app = Bridge::create($container);

// Dependencies
require BOOT_PATH . 'dependencies.php';
// Modules
require BOOT_PATH . 'modules.php';
// Middleware
require BOOT_PATH . 'middleware.php';
// Routes
$app->group('/api', function (RouteCollectorProxy $api) {
    require BOOT_PATH . '/routes.php';
});
// Error Handler
/** @var \Slim\Middleware\ErrorMiddleware $errorMiddleware */
$errorMiddleware = $app->addErrorMiddleware(true, true, true, $container->get(LoggerInterface::class));
$errorMiddleware->setDefaultErrorHandler(
    new ApiErrorHandler(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        $container->get(LoggerInterface::class)
    )
);

return $app;
