<?php

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Set up constants
require __DIR__ . '/constants.php';

// Composer autoloader
require VENDOR_PATH . 'autoload.php';

$app = AppFactory::create();

$app->group('/api', function (RouteCollectorProxy $api) {
    require BOOT_PATH . '/routes.php';
});

return $app;
