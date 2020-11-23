<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\EditQueueHandler;
use Ticket\Service\QueueManager;

class EditQueueHandlerFactory
{
    public function __invoke(ContainerInterface $container): EditQueueHandler
    {
        $queueManager = $container->get(QueueManager::class);
        $renderer     = $container->get(TemplateRendererInterface::class);
        $urlHelper    = $container->get(UrlHelper::class);

        return new EditQueueHandler($queueManager, $renderer, $urlHelper);
    }
}
