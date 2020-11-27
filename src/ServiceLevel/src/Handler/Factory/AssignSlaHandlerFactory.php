<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\AssignSlaHandler;
use ServiceLevel\Service\SlaService;

class AssignSlaHandlerFactory
{
    public function __invoke(ContainerInterface $container): AssignSlaHandler
    {
        $slaService          = $container->get(SlaService::class);
        $organisationService = $container->get(OrganisationManager::class);
        $renderer            = $container->get(TemplateRendererInterface::class);
        $urlHelper           = $container->get(UrlHelper::class);

        return new AssignSlaHandler($slaService, $organisationService, $renderer, $urlHelper);
    }
}
