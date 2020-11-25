<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\ListBusinessHoursHandler;
use ServiceLevel\Service\SlaService;

class ListBusinessHoursHandlerFactory
{
    public function __invoke(ContainerInterface $container): ListBusinessHoursHandler
    {
        $slaService = $container->get(SlaService::class);
        $renderer   = $container->get(TemplateRendererInterface::class);
        $urlHelper  = $container->get(UrlHelper::class);

        return new ListBusinessHoursHandler($slaService, $renderer, $urlHelper);
    }
}
