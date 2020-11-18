<?php

declare(strict_types=1);

namespace UserAuthentication\Handler\Factory;

use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use UserAuthentication\Handler\LoginPageHandler;

class LoginPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginPageHandler
    {
        /** @var UrlHelper $helper */
        $helper = $container->get(UrlHelper::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var StorageInterface $cache */
        $cache = $container->get(StorageInterface::class);

        return new LoginPageHandler($renderer, $helper, $cache);
    }
}
