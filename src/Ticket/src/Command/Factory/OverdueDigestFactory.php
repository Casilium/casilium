<?php

declare(strict_types=1);

namespace Ticket\Command\Factory;

use Logger\Service\LogService;
use MailService\Service\MailService;
use Psr\Container\ContainerInterface;
use Ticket\Command\OverdueDigest;
use Ticket\Service\TicketService;

class OverdueDigestFactory
{
    public function __invoke(ContainerInterface $container): OverdueDigest
    {
        return new OverdueDigest(
            $container->get(TicketService::class),
            $container->get(MailService::class),
            $container->get(LogService::class)
        );
    }
}
