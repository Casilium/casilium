<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Organisation\Handler\OrganisationEditHandler;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class OrganisationEditHandlerFactory
{
    public function __invoke(ContainerInterface $container) : OrganisationEditHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        $renderer = $container->get(TemplateRendererInterface::class);
        $urlHelper = $container->get(UrlHelper::class);
        return new OrganisationEditHandler($organisationManager, $renderer, $urlHelper);
    }
}