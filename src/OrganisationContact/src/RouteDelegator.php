<?php

declare(strict_types=1);

namespace OrganisationContact;

use OrganisationContact\Handler\CreateContactHandler;
use OrganisationContact\Handler\EditContactHandler;
use OrganisationContact\Handler\ListContactHandler;
use OrganisationContact\Handler\ViewContactHandler;
use Psr\Container\ContainerInterface;
use Mezzio\Application;

class RouteDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/contact/create',
        CreateContactHandler::class, ['GET', 'POST'],
            'contact.create'
        );

        $app->route(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/contact/edit/{contact_id:[\d]+}',
            EditContactHandler::class, ['GET', 'POST'],
            'contact.edit'
        );

        $app->route(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/contact/view/{contact_id:[\d]+}',
            ViewContactHandler::class, ['GET'],
            'contact.view'
        );

        $app->get(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/contact/list',
            ListContactHandler::class,
            'contact.list'
        );


        return $app;
    }
}