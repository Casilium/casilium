<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\AssignQueueMembersHandler;
use Ticket\Service\QueueManager;

class AssignQueueMembersHandlerFactory
{
    public function __invoke(ContainerInterface $container): AssignQueueMembersHandler
    {
        $queueManager = $container->get(QueueManager::class);
        $renderer     = $container->get(TemplateRendererInterface::class);
        $urlHelper    = $container->get(UrlHelper::class);

        return new AssignQueueMembersHandler($queueManager, $renderer, $urlHelper);
    }
}
