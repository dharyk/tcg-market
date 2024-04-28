<?php

use TcgMarket\Middleware\RateLimiterMiddleware;
use TcgMarket\Middleware\SessionMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

/**
 * Application middleware
 */
$app->add(new SessionMiddleware(
    $container->get('settings')['session']
));
$app->add($container->get(RateLimiterMiddleware::class));
$app->add(new WhoopsMiddleware([
    'enable' => true,
    'editor' => 'vscode',
    'title'  => 'Whoopsie! Server lost its marbles...',
]));
