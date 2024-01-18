<?php

declare(strict_types=1);

namespace OrganisationContact\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationContact\Handler\ListContactHandler;
use OrganisationContact\Service\ContactService;
use Psr\Container\ContainerInterface;

class ListContactHandlerFactory
{
    public function __invoke(ContainerInterface $container): ListContactHandler
    {
        $service  = $container->get(ContactService::class);
        $renderer = $container->get(TemplateRendererInterface::class);
        $helper   = $container->get(UrlHelper::class);

        return new ListContactHandler($service, $renderer, $helper);
    }
}
