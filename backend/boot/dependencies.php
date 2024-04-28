<?php

use Aura\Sql\ExtendedPdo;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use TcgMarket\Core\DatabaseInterface;
use TcgMarket\Handler\CacheHandler;
use TcgMarket\Handler\DatabaseHandler;
use TcgMarket\Middleware\SessionMiddleware;

/**
 * Application dependencies
 */
// Default logger
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
// Default cache handler
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
// Session middleware
$container->set(
    SessionMiddleware::class,
    static function (ContainerInterface $container) {
        $settings = $container->get('settings')['session'];

        return new SessionMiddleware($settings);
    }
);
// Default database handler
$container->set(
    DatabaseInterface::class,
    static function (ContainerInterface $container) {
        $settings = $container->get('settings')['database'];
        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_connection=utf8mb4, character_set_client=utf8mb4, sql_mode="NO_UNSIGNED_SUBTRACTION"',
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => true,
        ];
        $dsn = sprintf(
            '%s:dbname=%s;host=%s;port=%s;charset=utf8',
            $settings['driver'],
            $settings['dbname'],
            $settings['host'],
            $settings['port']
        );

        return new DatabaseHandler(
            new ExtendedPdo(
                $dsn,
                $settings['username'],
                $settings['password'],
                $options
            )
        );
    }
);
