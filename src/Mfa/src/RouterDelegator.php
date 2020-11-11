<?php

declare(strict_types=1);

namespace Mfa;

use Psr\Container\ContainerInterface;
use Mezzio\Application;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();
        $app->route(
            '/mfa/enable',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\EnableMfaHandler::class,
            ],
            ['GET','POST'],
            'mfa.enable'
        );

        $app->route(
            '/mfa/validate',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\ValidateMfaHandler::class,
            ],
            ['GET','POST'],
            'mfa.validate'
        );

        return $app;
    }
}