<?php

use TcgMarket\Middleware\RateLimiterMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

/**
 * Application middleware
 */

$app->add($container->get(RateLimiterMiddleware::class));

$app->add(new WhoopsMiddleware([
    'enable' => true,
    'editor' => 'vscode',
    'title'  => 'Whoopsie! Server lost its marbles...',
]));
