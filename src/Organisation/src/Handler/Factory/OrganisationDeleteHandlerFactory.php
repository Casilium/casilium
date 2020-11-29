<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Interop\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Handler\OrganisationDeleteHandler;
use Organisation\Service\OrganisationManager;

class OrganisationDeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationDeleteHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        $render              = $container->get(TemplateRendererInterface::class);
        $helper              = $container->get(UrlHelper::class);
        return new OrganisationDeleteHandler($organisationManager, $render, $helper);
    }
}
