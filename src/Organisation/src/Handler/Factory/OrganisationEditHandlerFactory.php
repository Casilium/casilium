<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Handler\OrganisationEditHandler;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;

class OrganisationEditHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationEditHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        $renderer            = $container->get(TemplateRendererInterface::class);
        $urlHelper           = $container->get(UrlHelper::class);
        return new OrganisationEditHandler($organisationManager, $renderer, $urlHelper);
    }
}
