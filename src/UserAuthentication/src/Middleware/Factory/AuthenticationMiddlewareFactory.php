<?php

declare(strict_types=1);

namespace UserAuthentication\Middleware\Factory;

use Psr\Container\ContainerInterface;
use UserAuthentication\Middleware\AuthenticationMiddleware;
use UserAuthentication\Service\AuthenticationService;

class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        $authService = $container->get(AuthenticationService::class);
        return new AuthenticationMiddleware($authService);
    }
}