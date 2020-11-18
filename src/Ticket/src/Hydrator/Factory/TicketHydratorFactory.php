<?php

declare(strict_types=1);

namespace Ticket\Hydrator\Factory;

use Psr\Container\ContainerInterface;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;

class TicketHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $ticketService = $container->get(TicketService::class);
        return new TicketHydrator($ticketService);
    }
}
