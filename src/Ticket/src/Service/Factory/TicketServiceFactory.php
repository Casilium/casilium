<?php

declare(strict_types=1);

namespace Ticket\Service\Factory;

use Doctrine\ORM\EntityManager;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\LazyListener;
use MailService\Service\MailService;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;
use Ticket\EventListener\TicketEventListener;
use Ticket\Service\QueueManager;
use Ticket\Service\TicketService;
use User\Service\UserManager;

class TicketServiceFactory
{
    public function __invoke(ContainerInterface $container): TicketService
    {
        $entityManager       = $container->get(EntityManager::class);
        $organisationManager = $container->get(OrganisationManager::class);
        $siteManager         = $container->get(SiteManager::class);
        $contactManager      = $container->get(ContactService::class);
        $queueManager        = $container->get(QueueManager::class);
        $userManager         = $container->get(UserManager::class);
        $mailService         = $container->get(MailService::class);

        $events = new EventManager();
        $events->setIdentifiers([
            TicketService::class,
        ]);

        $lazyListener = new LazyListener([
            'listener' => TicketEventListener::class,
            'method'   => 'onTicketCreated',
        ], $container);
        $events->attach('ticket.created', $lazyListener);

        $lazyListener = new LazyListener([
            'listener' => TicketEventListener::class,
            'method'   => 'onTicketReply',
        ], $container);
        $events->attach('ticket.reply', $lazyListener);

        return new TicketService(
            $events,
            $entityManager,
            $organisationManager,
            $siteManager,
            $contactManager,
            $queueManager,
            $userManager,
            $mailService
        );
    }
}
