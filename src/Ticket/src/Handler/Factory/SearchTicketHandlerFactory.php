<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\SearchTicketHandler;
use Ticket\Service\TicketService;

class SearchTicketHandlerFactory
{
    public function __invoke(ContainerInterface $container): SearchTicketHandler
    {
        $ticketService = $container->get(TicketService::class);
        $renderer      = $container->get(TemplateRendererInterface::class);

        return new SearchTicketHandler($ticketService, $renderer);
    }
}
