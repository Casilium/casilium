<?php

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\CreateTicketHandler;
use Ticket\Handler\EditTickerHandler;
use Ticket\Handler\ViewTicketHandler;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;

class ViewTicketHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $ticketService = $container->get(TicketService::class);
        $hydrator      = $container->get(TicketHydrator::class);
        $renderer      = $container->get(TemplateRendererInterface::class);

        return new ViewTicketHandler($ticketService, $hydrator, $renderer);
    }
}
