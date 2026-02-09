<?php

declare(strict_types=1);

namespace Ticket\Command\Factory;

use Psr\Container\ContainerInterface;
use Ticket\Command\CloseResolvedTickets;
use Ticket\Service\TicketService;

class CloseResolvedTicketsFactory
{
    public function __invoke(ContainerInterface $container): CloseResolvedTickets
    {
        $ticketService = $container->get(TicketService::class);
        $config        = $container->get('config');
        $autoCloseDays = (int) ($config['tickets']['auto_close_days'] ?? 2);
        return new CloseResolvedTickets($ticketService, $autoCloseDays);
    }
}
