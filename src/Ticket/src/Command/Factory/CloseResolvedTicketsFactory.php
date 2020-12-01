<?php
declare(strict_types=1);

namespace Ticket\Command\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Ticket\Command\CloseResolvedTickets;
use Ticket\Command\CreateTicketsFromEmail;
use Ticket\Service\TicketService;

class CloseResolvedTicketsFactory
{
    public function __invoke(ContainerInterface $container): CloseResolvedTickets
    {
        $ticketService = $container->get(TicketService::class);
        return new CloseResolvedTickets($ticketService);
    }
}
