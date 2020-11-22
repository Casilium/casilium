<?php

declare(strict_types=1);

namespace Logger\Service;

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use function getcwd;

class LogService
{
    public function __invoke(ContainerInterface $container): Logger
    {
        $logger = new Logger('log');

        $path = getcwd() . '/data/log/log.txt';

        $logger->pushHandler(new StreamHandler($path), Logger::DEBUG);
        $logger->pushHandler(new FirePHPHandler());

        return $logger;
    }
}
