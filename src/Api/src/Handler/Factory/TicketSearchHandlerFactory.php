<?php

declare(strict_types=1);

namespace Api\Handler\Factory;

use Api\Handler\TicketSearchHandler;
use Psr\Container\ContainerInterface;
use Ticket\Service\TicketService;

class TicketSearchHandlerFactory
{
    public function __invoke(ContainerInterface $container): TicketSearchHandler
    {
        $ticketService = $container->get(TicketService::class);
        return new TicketSearchHandler($ticketService);
    }
}
