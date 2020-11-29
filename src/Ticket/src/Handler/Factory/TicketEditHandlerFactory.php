<?php

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\EditTickerHandler;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;

class TicketEditHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $ticketService = $container->get(TicketService::class);
        $hydrator      = $container->get(TicketHydrator::class);
        $renderer      = $container->get(TemplateRendererInterface::class);
        $urlHelper     = $container->get(UrlHelper::class);

        return new EditTickerHandler($ticketService, $hydrator, $renderer, $urlHelper);
    }
}
