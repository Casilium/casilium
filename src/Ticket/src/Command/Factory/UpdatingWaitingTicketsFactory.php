<?php
declare(strict_types=1);

namespace Ticket\Command\Factory;

use Psr\Container\ContainerInterface;
use Ticket\Command\UpdateWaitingTickets;
use Ticket\Service\TicketService;

class UpdatingWaitingTicketsFactory
{
    public function __invoke(ContainerInterface $container): UpdateWaitingTickets
    {
        $ticketService = $container->get(TicketService::class);
        return new UpdateWaitingTickets($ticketService);
    }
}
