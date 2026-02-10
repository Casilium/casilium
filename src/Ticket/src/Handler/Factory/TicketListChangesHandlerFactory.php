<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Psr\Container\ContainerInterface;
use Ticket\Handler\TicketListChangesHandler;
use Ticket\Service\TicketService;

class TicketListChangesHandlerFactory
{
    public function __invoke(ContainerInterface $container): TicketListChangesHandler
    {
        $ticketService = $container->get(TicketService::class);

        return new TicketListChangesHandler($ticketService);
    }
}
