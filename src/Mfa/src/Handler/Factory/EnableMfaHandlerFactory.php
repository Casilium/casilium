<?php

declare(strict_types=1);

namespace Mfa\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Mfa\Handler;
use Mfa\Service\MfaService;
use Psr\Container\ContainerInterface;
use User\Service\UserManager;

/**
 * Displays the form to enable MFA
 */
class EnableMfaHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\EnableMfaHandler
    {
        /** @var UserManager $userManager */
        $userManager = $container->get(UserManager::class);

        /** @var MfaService $mfaService */
        $mfaService = $container->get(MfaService::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get(UrlHelper::class);
        return new Handler\EnableMfaHandler($mfaService, $userManager, $renderer, $urlHelper);
    }
}
