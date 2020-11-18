<?php

declare(strict_types=1);

namespace Account\Handler\Factory;

use Account\Handler\ChangePasswordHandler;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Service\UserManager;

class ChangePasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $userManager = $container->get(UserManager::class);
        $renderer    = $container->get(TemplateRendererInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);

        return new ChangePasswordHandler($userManager, $renderer, $urlHelper);
    }
}
