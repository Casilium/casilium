<?php

declare(strict_types=1);

namespace Account;

use Psr\Container\ContainerInterface;
use Mezzio\Application;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->get('/account', Handler\AccountPageHandler::class, 'account');

        return $app;
    }
}