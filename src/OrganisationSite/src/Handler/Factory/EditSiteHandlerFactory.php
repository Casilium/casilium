<?php

declare(strict_types=1);

namespace OrganisationSite\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Handler\EditSiteHandler;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;

class EditSiteHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $siteManager = $container->get(SiteManager::class);
        $renderer    = $container->get(TemplateRendererInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);
        return new EditSiteHandler($siteManager, $renderer, $urlHelper);
    }
}
