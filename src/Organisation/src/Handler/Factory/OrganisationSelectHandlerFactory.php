<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Handler\OrganisationSelectHandler;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;

class OrganisationSelectHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationSelectHandler
    {
        $router   = $container->get(RouterInterface::class);
        $manager  = $container->get(OrganisationManager::class);
        $renderer = $container->get(TemplateRendererInterface::class);

        return new OrganisationSelectHandler($router, $manager, $renderer);
    }
}
