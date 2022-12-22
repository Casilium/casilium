<?php

declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mfa\Handler\DisableMfaHandler;
use Mfa\Service\MfaService;
use Psr\Container\ContainerInterface;

class DisableMfaHandlerFactory
{
    public function __invoke(ContainerInterface $container): DisableMfaHandler
    {
        $mfaService = $container->get(MfaService::class);
        $urlHelper  = $container->get(UrlHelper::class);

        return new DisableMfaHandler($mfaService, $urlHelper);
    }
}
