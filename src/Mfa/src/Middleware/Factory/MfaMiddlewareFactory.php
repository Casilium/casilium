<?php

declare(strict_types=1);

namespace Mfa\Middleware\Factory;

use Mezzio\Helper\UrlHelper;
use Mfa\Middleware\MfaMiddleware;
use Mfa\Service\MfaService;
use Psr\Container\ContainerInterface;

class MfaMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MfaMiddleware
    {
        $mfaService = $container->get(MfaService::class);
        $urlHelper  = $container->get(UrlHelper::class);
        return new MfaMiddleware($mfaService, $urlHelper);
    }
}
