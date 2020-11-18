<?php

declare(strict_types=1);

namespace Ticket\Service\Factory;

use Doctrine\ORM\EntityManager;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;
use Ticket\Service\QueueManager;
use Ticket\Service\TicketService;
use User\Service\UserManager;

class TicketServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager       = $container->get(EntityManager::class);
        $organisationManager = $container->get(OrganisationManager::class);
        $siteManager         = $container->get(SiteManager::class);
        $contactManager      = $container->get(ContactService::class);
        $queueManager        = $container->get(QueueManager::class);
        $userManager         = $container->get(UserManager::class);

        return new TicketService(
            $entityManager,
            $organisationManager,
            $siteManager,
            $contactManager,
            $queueManager,
            $userManager
        );
    }
}
