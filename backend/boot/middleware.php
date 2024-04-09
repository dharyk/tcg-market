<?php

use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

/**
 * Application middleware
 */

$app->add(new WhoopsMiddleware([
    'enable' => true,
    'editor' => 'vscode',
    'title'  => 'Whoopsie! Server lost its marbles...',
]));
