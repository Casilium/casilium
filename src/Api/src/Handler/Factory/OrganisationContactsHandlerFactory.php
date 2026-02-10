<?php

declare(strict_types=1);

namespace Api\Handler\Factory;

use Api\Handler\OrganisationContactsHandler;
use Psr\Container\ContainerInterface;
use Ticket\Service\TicketService;

class OrganisationContactsHandlerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationContactsHandler
    {
        $ticketService = $container->get(TicketService::class);
        return new OrganisationContactsHandler($ticketService);
    }
}
