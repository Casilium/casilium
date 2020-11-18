<?php
declare(strict_types=1);

namespace OrganisationSite;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        // create organisation site
        $app->route(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/site/create',
            Handler\CreateSiteHandler::class,
            ['GET', 'POST'],
            'organisation_site.create'
        );

        $app->route(
            '/organisation/sites/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/edit',
            Handler\EditSiteHandler::class,
            ['GET', 'POST'],
            'organisation_site.edit'
        );

        $app->get(
            '/organisation/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/sites',
            Handler\ListSiteHandler::class,
            'organisation_site.list'
        );

        $app->get(
            '/organisation/sites/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}[/]',
            Handler\ViewSiteHandler::class,
            'organisation_site.read'
        );

        $app->get(
            '/organisation/sites/{id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/delete[/{confirm}]',
            Handler\DeleteSiteHandler::class,
            'organisation_site.delete'
        );

        return $app;
    }
}
