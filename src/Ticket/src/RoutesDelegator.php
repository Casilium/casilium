<?php
declare(strict_types=1);

namespace Ticket;

use Psr\Container\ContainerInterface;
use Mezzio\Application;
use Ticket\Handler\CreateTicketHandler;
use Ticket\Handler\ListTicketHandler;

class RoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param string $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route(
            '/organisation/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/ticket/create',
        CreateTicketHandler::class,
            ['GET', 'POST'],
            'ticket.create'
        );

        $app->get('/ticket/list', ListTicketHandler::class, 'ticket.list');

        return $app;
    }
}