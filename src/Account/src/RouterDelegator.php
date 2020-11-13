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

        $app->route('/account/change-password',
            Handler\ChangePasswordHandler::class, ['GET', 'POST'], 'account.change_password');
        return $app;
    }
}