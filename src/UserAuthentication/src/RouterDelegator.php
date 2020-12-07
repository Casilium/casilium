<?php

declare(strict_types=1);

namespace UserAuthentication;

use App\Middleware\PrgMiddleware;
use Mezzio\Application;
use Mfa\Middleware\MfaMiddleware;
use Psr\Container\ContainerInterface;
use UserAuthentication\Handler\LogoutPageHandler;
use UserAuthentication\Middleware\AuthenticationMiddleware;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route('/login', [
            PrgMiddleware::class,
            Handler\LoginPageHandler::class,
            AuthenticationMiddleware::class,
            MfaMiddleware::class,
        ], ['GET', 'POST'], 'login');

        $app->get('/logout', LogoutPageHandler::class, 'logout');
        return $app;
    }
}
