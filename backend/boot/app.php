<?php

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Set up constants
require __DIR__ . '/constants.php';

// Composer autoloader
require VENDOR_PATH . 'autoload.php';

$appConfig = require BOOT_PATH . 'config.php';

$app = AppFactory::create();

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

return $app;
