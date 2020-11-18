<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Handler\OrganisationCreateHandler;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;

class OrganisationCreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationCreateHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        $renderer            = $container->get(TemplateRendererInterface::class);
        $urlHelper           = $container->get(UrlHelper::class);

        return new OrganisationCreateHandler($organisationManager, $renderer, $urlHelper);
    }
}
