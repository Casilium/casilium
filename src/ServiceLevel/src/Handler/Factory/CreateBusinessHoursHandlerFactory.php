<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\CreateBusinessHoursHandler;
use ServiceLevel\Service\SlaService;

class CreateBusinessHoursHandlerFactory
{
    public function __invoke(ContainerInterface $container): CreateBusinessHoursHandler
    {
        $slaService = $container->get(SlaService::class);
        $renderer   = $container->get(TemplateRendererInterface::class);
        $urlHelper  = $container->get(UrlHelper::class);

        return new CreateBusinessHoursHandler($slaService, $renderer, $urlHelper);
    }
}
