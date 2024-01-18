<?php

declare(strict_types=1);

namespace OrganisationSite\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Handler\ViewSiteHandler;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;

class ViewSiteHandlerFactory
{
    public function __invoke(ContainerInterface $container): ViewSiteHandler
    {
        $siteManager = $container->get(SiteManager::class);
        $renderer    = $container->get(TemplateRendererInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);
        return new ViewSiteHandler($siteManager, $renderer, $urlHelper);
    }
}
