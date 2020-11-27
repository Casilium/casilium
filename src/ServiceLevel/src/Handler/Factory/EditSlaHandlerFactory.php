<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\EditSlaHandler;
use ServiceLevel\Hydrator\SlaHydrator;
use ServiceLevel\Service\SlaService;

class EditSlaHandlerFactory
{
    public function __invoke(ContainerInterface $container): EditSlaHandler
    {
        $slaService = $container->get(SlaService::class);
        $hydrator   = $container->get(SlaHydrator::class);
        $renderer   = $container->get(TemplateRendererInterface::class);
        $urlHelper  = $container->get(UrlHelper::class);

        return new EditSlaHandler($slaService, $hydrator, $renderer, $urlHelper);
    }
}
