<?php

declare(strict_types=1);

namespace Ticket\Command\Factory;

use Logger\Service\LogService;
use MailService\Service\MailService;
use Psr\Container\ContainerInterface;
use Ticket\Command\Notifications;
use Ticket\Service\TicketService;

class NotificationsFactory
{
    public function __invoke(ContainerInterface $container): Notifications
    {
        return new Notifications(
            $container->get(TicketService::class),
            $container->get(MailService::class),
            $container->get(LogService::class)
        );
    }
}
