<?php

declare(strict_types=1);

namespace Mfa;

use Mezzio\Application;
use Mezzio\Csrf\CsrfMiddleware;
use Mfa\Handler\DisableMfaHandler;
use Psr\Container\ContainerInterface;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();
        $app->route(
            '/mfa/enable',
            [
                CsrfMiddleware::class,
                Handler\EnableMfaHandler::class,
            ],
            ['GET', 'POST'],
            'mfa.enable'
        );

        $app->route(
            '/mfa/validate',
            [
                CsrfMiddleware::class,
                Handler\ValidateMfaHandler::class,
            ],
            ['GET', 'POST'],
            'mfa.validate'
        );

        $app->get('/mfa/disable', DisableMfaHandler::class, 'mfa.disable');

        return $app;
    }
}
