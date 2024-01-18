<?php

declare(strict_types=1);

namespace OrganisationSite\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Handler\CreateSiteHandler;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;

class CreateSiteHandlerFactory
{
    public function __invoke(ContainerInterface $container): CreateSiteHandler
    {
        $siteManager = $container->get(SiteManager::class);
        $renderer    = $container->get(TemplateRendererInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);
        return new CreateSiteHandler($siteManager, $renderer, $urlHelper);
    }
}
