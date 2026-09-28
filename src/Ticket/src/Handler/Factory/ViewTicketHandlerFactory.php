<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\ViewTicketHandler;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\CannedResponseManager;
use Ticket\Service\TicketService;

class ViewTicketHandlerFactory
{
    public function __invoke(ContainerInterface $container): ViewTicketHandler
    {
        $ticketService         = $container->get(TicketService::class);
        $hydrator              = $container->get(TicketHydrator::class);
        $renderer              = $container->get(TemplateRendererInterface::class);
        $urlHelper             = $container->get(UrlHelper::class);
        $cannedResponseManager = $container->get(CannedResponseManager::class);

        return new ViewTicketHandler(
            $ticketService,
            $hydrator,
            $renderer,
            $urlHelper,
            $cannedResponseManager
        );
    }
}
