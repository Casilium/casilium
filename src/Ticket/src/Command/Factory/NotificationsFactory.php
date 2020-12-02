<?php
declare(strict_types=1);

namespace Ticket\Command\Factory;

use MailService\Service\MailService;
use Psr\Container\ContainerInterface;
use Ticket\Command\Notifications;
use Ticket\Service\TicketService;

class NotificationsFactory
{
    public function __invoke(ContainerInterface $container): Notifications
    {
        $ticketService = $container->get(TicketService::class);
        $mailServce    = $container->get(MailService::class);
        return new Notifications($ticketService, $mailServce);
    }
}
