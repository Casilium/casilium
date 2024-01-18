<?php

declare(strict_types=1);

namespace Account\Handler\Factory;

use Account\Handler\AccountPageHandler;
use interop\container\containerinterface;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Service\MfaService;

class AccountPageHandlerFactory
{
    public function __invoke(containerinterface $container): AccountPageHandler
    {
        $renderer   = $container->get(TemplateRendererInterface::class);
        $mfaService = $container->get(MfaService::class);

        return new AccountPageHandler($renderer, $mfaService);
    }
}
