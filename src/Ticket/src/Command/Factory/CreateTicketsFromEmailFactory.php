<?php

declare(strict_types=1);

namespace Ticket\Command\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Logger\Service\LogService;
use Psr\Container\ContainerInterface;
use Ticket\Command\CreateTicketsFromEmail;
use Ticket\Service\TicketService;

class CreateTicketsFromEmailFactory
{
    public function __invoke(ContainerInterface $container): CreateTicketsFromEmail
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        $ticketService = $container->get(TicketService::class);
        $logger        = $container->get(LogService::class);
        $config        = $container->get('config');
        return new CreateTicketsFromEmail($entityManager, $ticketService, $logger, $config);
    }
}
