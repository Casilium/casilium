<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\ViewSlaHandler;
use ServiceLevel\Service\SlaService;

class ViewSlaHandlerFactory
{
    public function __invoke(ContainerInterface $container): ViewSlaHandler
    {
        $slaService = $container->get(SlaService::class);
        $renderer   = $container->get(TemplateRendererInterface::class);
        $urlHelper  = $container->get(UrlHelper::class);

        return new ViewSlaHandler($slaService, $renderer, $urlHelper);
    }
}
