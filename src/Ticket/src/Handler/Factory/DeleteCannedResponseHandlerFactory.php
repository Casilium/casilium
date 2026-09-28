<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\DeleteCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class DeleteCannedResponseHandlerFactory
{
    public function __invoke(ContainerInterface $container): DeleteCannedResponseHandler
    {
        return new DeleteCannedResponseHandler(
            $container->get(CannedResponseManager::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(UrlHelper::class)
        );
    }
}
