<?php
declare(strict_types=1);

namespace Ticket;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use Ticket\Handler\CreateQueueHandler;
use Ticket\Handler\CreateTicketHandler;
use Ticket\Handler\EditQueueHandler;
use Ticket\Handler\EditTickerHandler;
use Ticket\Handler\ListQueueHandler;
use Ticket\Handler\ListTicketHandler;
use Ticket\Handler\ViewTicketHandler;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route(
            '/organisation/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/ticket/create',
            CreateTicketHandler::class,
            ['GET', 'POST'],
            'ticket.create'
        );

        $app->route(
            '/ticket/{ticket_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/edit',
            EditTickerHandler::class,
            ['GET', 'POST'],
            'ticket.edit'
        );

        $app->route(
            '/ticket/{ticket_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}',
            ViewTicketHandler::class,
            ['GET', 'POST'],
            'ticket.view'
        );


        $app->get(
            '/ticket/list[/organisation/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}]',
            ListTicketHandler::class,
            'ticket.list'
        );

        $app->route(
            '/admin/ticket/queue/create',
            [
                CreateQueueHandler::class,
            ],
            ['GET', 'POST'],
            'admin.queue_create'
        );
        $app->route(
            '/admin/ticket/queue/edit/{id:\d}',
            [
                EditQueueHandler::class,
            ],
            ['GET', 'POST'],
            'admin.queue_edit'
        );

        $app->get('/admin/ticket/queue/list', ListQueueHandler::class, 'admin.queue_list');


        return $app;
    }
}
