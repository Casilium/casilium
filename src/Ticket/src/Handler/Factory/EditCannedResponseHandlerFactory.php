<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Doctrine\ORM\EntityManager;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\EditCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class EditCannedResponseHandlerFactory
{
    public function __invoke(ContainerInterface $container): EditCannedResponseHandler
    {
        return new EditCannedResponseHandler(
            $container->get(CannedResponseManager::class),
            $container->get(EntityManager::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(UrlHelper::class)
        );
    }
}
