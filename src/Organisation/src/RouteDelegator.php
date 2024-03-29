<?php

declare(strict_types=1);

namespace Organisation;

use Mezzio\Application;
use Organisation\Handler\ExportHandler;
use Organisation\Handler\OrganisationCreateHandler;
use Organisation\Handler\OrganisationDeleteHandler;
use Organisation\Handler\OrganisationEditHandler;
use Organisation\Handler\OrganisationListHandler;
use Organisation\Handler\OrganisationReadHandler;
use Organisation\Handler\OrganisationSelectHandler;
use Psr\Container\ContainerInterface;

class RouteDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->get(
            '/organisation[/]',
            OrganisationListHandler::class,
            'organisation.list'
        );

        $app->route(
            '/organisation/create',
            OrganisationCreateHandler::class,
            ['GET', 'POST'],
            'organisation.create'
        );

        $app->route(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/edit[/]',
            OrganisationEditHandler::class,
            ['GET', 'POST'],
            'organisation.edit'
        );

        $app->get(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}[/]',
            OrganisationReadHandler::class,
            'organisation.view'
        );

        $app->route(
            '/organisation/delete/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}[/]',
            OrganisationDeleteHandler::class,
            ['GET', 'POST'],
            'organisation.delete'
        );

        $app->route(
            '/organisation/select',
            OrganisationSelectHandler::class,
            ['GET', 'POST'],
            'organisation.select'
        );

        $app->get('/organisation/export', ExportHandler::class, 'organisation.export');

        return $app;
    }
}
