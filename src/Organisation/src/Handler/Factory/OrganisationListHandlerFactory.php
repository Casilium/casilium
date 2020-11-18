<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Organisation\Handler\OrganisationListHandler;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;

class OrganisationListHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationListHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        $renderer            = $container->get(TemplateRendererInterface::class);
        return new OrganisationListHandler($organisationManager, $renderer);
    }
}
