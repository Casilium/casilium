<?php

declare(strict_types=1);

namespace OrganisationSite\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Handler\DeleteSiteHandler;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;

class DeleteSiteHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $siteManager = $container->get(SiteManager::class);
        $renderer    = $container->get(TemplateRendererInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);
        return new DeleteSiteHandler($siteManager, $renderer, $urlHelper);
    }
}
