<?php

declare(strict_types=1);

namespace Ticket\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Ticket\Handler\GoToTicketHandler;
use Ticket\Service\TicketService;

class GoToTicketHandlerFactory
{
    public function __invoke(ContainerInterface $container): GoToTicketHandler
    {
        $ticketService = $container->get(TicketService::class);
        $renderer      = $container->get(TemplateRendererInterface::class);
        $urlHelper     = $container->get(UrlHelper::class);
        return new GoToTicketHandler($ticketService, $renderer, $urlHelper);
    }
}
