<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\ListTicketHandler;
use Ticket\Service\TicketService;

class ListTickerHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $ticketService = $container->get(TicketService::class);
        $renderer = $container->get(TemplateRendererInterface::class);

        return new ListTicketHandler($ticketService, $renderer);
    }
}