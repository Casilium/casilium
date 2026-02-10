<?php

declare(strict_types=1);

namespace Api;

use Api\Handler\OrganisationContactsHandler;
use Api\Handler\TicketSearchHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->get(
            '/api/organisation/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/contacts',
            OrganisationContactsHandler::class,
            'api.organisation_contacts'
        );

        $app->get('/api/ticket-search', TicketSearchHandler::class, 'api.ticket_search');

        return $app;
    }
}
