<?php

declare(strict_types=1);

namespace UserAuthentication\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Service\MfaService;
use Psr\Container\ContainerInterface;
use UserAuthentication\Handler\LoginPageHandler;
use UserAuthentication\Service\AuthenticationService;

class LoginPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginPageHandler
    {
        $authService = $container->get(AuthenticationService::class);
        $mfaService  = $container->get(MfaService::class);
        $helper      = $container->get(UrlHelper::class);
        $renderer    = $container->get(TemplateRendererInterface::class);

        return new LoginPageHandler($authService, $mfaService, $renderer, $helper);
    }
}
