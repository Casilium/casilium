<?php

declare(strict_types=1);

namespace Api;

use Api\Handler\TicketSearchHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->get('/api/ticket-search', TicketSearchHandler::class, 'api.ticket_search');

        return $app;
    }
}
