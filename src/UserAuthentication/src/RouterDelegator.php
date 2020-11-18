<?php

declare(strict_types=1);

namespace UserAuthentication;

use Mezzio\Application;
use Mezzio\Authentication\AuthenticationMiddleware;
use Psr\Container\ContainerInterface;
use UserAuthentication\Handler\LogoutPageHandler;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route('/login', [
            Handler\LoginPageHandler::class,
            AuthenticationMiddleware::class,
        ], ['GET', 'POST'], 'login');

        $app->get('/logout', LogoutPageHandler::class, 'logout');
        return $app;
    }
}
