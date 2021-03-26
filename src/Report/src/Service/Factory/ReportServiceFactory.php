<?php

declare(strict_types=1);

namespace Report\Service\Factory;

use Doctrine\ORM\EntityManager;
use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;
use Report\Service\ReportService;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;

class ReportServiceFactory
{
    public function __invoke(ContainerInterface $container): ReportService
    {
        /** @var TicketRepository $ticketRepository */
        $ticketRepository    = $container->get(EntityManager::class)->getRepository(Ticket::class);
        $organisationManager = $container->get(OrganisationManager::class);
        return new ReportService($ticketRepository, $organisationManager);
    }
}
