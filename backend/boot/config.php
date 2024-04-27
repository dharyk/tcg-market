<?php

// Application configuration
return [
    'settings' => [
        'debug' => (bool) getenv('DEBUG_MODE'),
        'displayErrorDetails' => (bool) getenv('ERROR_DETAILS'),
        'determineRouteBeforeAppMiddleware' => true,
        'outputBuffering' => 'append',
        'cache' => [
            'host' => 'cache', //getenv('REDIS_HOST') ?? 'cache',
            'port' => 6379, // (int) getenv('REDIS_PORT') ?? 6379,
        ],
        'logger' => [
            'name' => getenv('APP_NAME'),
            'level' => getenv('LOG_LEVEL'),
        ],
        'session' => [
            'name' => getenv('SESSION_NAME'),
            'lifetime' => getenv('SESSION_TTL'),
            'secure' => true,
            'httpOnly' => true,
        ],
        'authentication' => [
            'routes' => '/\/api\/.*/',
            'exclude' => '/\/api\/(authenticate|info|refresh).*/',
        ],
        // 'firebase' => json_decode(
        //     file_get_contents(BASE_PATH.getenv('FIREBASE_CREDENTIALS')),
        //     true
        // ),
        'database' => [
            'driver' => 'mysql',
            'host' => getenv('DATABASE_HOST'),
            'port' => '3306',
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASS'),
            'dbname' => getenv('DATABASE_NAME'),
        ],
    ],
];
