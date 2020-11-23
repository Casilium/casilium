<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\DeleteQueueHandler;
use Ticket\Service\QueueManager;

class DeleteQueueHandlerFactory
{
    public function __invoke(ContainerInterface $container): DeleteQueueHandler
    {
        $queueManager = $container->get(QueueManager::class);
        $renderer     = $container->get(TemplateRendererInterface::class);
        $urlHelper    = $container->get(UrlHelper::class);

        return new DeleteQueueHandler($queueManager, $renderer, $urlHelper);
    }
}
