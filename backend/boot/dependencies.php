<?php

use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use TcgMarket\Handler\CacheHandler;
use TcgMarket\Handler\SessionSaveHandler;
use TcgMarket\Middleware\SessionMiddleware;

/**
 * Application dependencies
 */

$container->set(
    LoggerInterface::class,
    static function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $handler = new ChromePHPHandler(
            $settings['level']
        );
        $handler->setFormatter(
            new ChromePHPFormatter()
        );
        $logger = new Logger(
            $settings['name']
        );
        $logger->pushHandler($handler);

        return $logger;
    }
);

$container->set(
    CacheInterface::class,
    static function (ContainerInterface $container) {
        $settings = $container->get('settings')['cache'];
        $client = new Redis();
        $client->pconnect($settings['host'], $settings['port']);
        $client->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        return new CacheHandler($client);
    }
);

$container->set(
    SessionMiddleware::class,
    static function (ContainerInterface $container) {
        $settings = $container->get('settings')['session'];

        return new SessionMiddleware($settings);
    }
);
