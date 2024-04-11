<?php

use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Application dependencies
 */

$container->set(
    LoggerInterface::class,
    function (ContainerInterface $container) {
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
