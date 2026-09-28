<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\ListCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class ListCannedResponseHandlerFactory
{
    public function __invoke(ContainerInterface $container): ListCannedResponseHandler
    {
        return new ListCannedResponseHandler(
            $container->get(CannedResponseManager::class),
            $container->get(TemplateRendererInterface::class)
        );
    }
}
