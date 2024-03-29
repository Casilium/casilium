<?php

declare(strict_types=1);

namespace OrganisationContact\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationContact\Handler\DeleteContactHandler;
use OrganisationContact\Service\ContactService;
use Psr\Container\ContainerInterface;

class DeleteContactHandlerFactory
{
    public function __invoke(ContainerInterface $container): DeleteContactHandler
    {
        $service  = $container->get(ContactService::class);
        $renderer = $container->get(TemplateRendererInterface::class);
        $helper   = $container->get(UrlHelper::class);

        return new DeleteContactHandler($service, $renderer, $helper);
    }
}
