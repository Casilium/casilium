<?php
declare(strict_types=1);

namespace ServiceLevel;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\CreateBusinessHoursHandler;
use ServiceLevel\Handler\CreateSlaHandler;
use ServiceLevel\Handler\DeleteBusinessHoursHandler;
use ServiceLevel\Handler\EditBusinessHoursHandler;
use ServiceLevel\Handler\EditSlaHandler;
use ServiceLevel\Handler\ListBusinessHoursHandler;
use ServiceLevel\Handler\ListSlaHandler;
use ServiceLevel\Handler\ViewSlaHandler;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->route(
            '/admin/sla/business-hours/create',
            CreateBusinessHoursHandler::class,
            ['GET', 'POST'],
            'admin.sla_create_business_hours'
        );

        $app->route(
            '/admin/sla/business-hours/edit/{id:\d}',
            EditBusinessHoursHandler::class,
            ['GET', 'POST'],
            'admin.sla_edit_business_hours'
        );

        $app->get(
            '/admin/sla/business-hours/list',
            ListBusinessHoursHandler::class,
            'admin.sla_list_business_hours'
        );

        $app->get(
            '/admin/sla/business-hours/delete/{id:\d}[/confirm/{confirm}]',
            DeleteBusinessHoursHandler::class,
            'admin.sla_delete_business_hours'
        );

        $app->route(
            '/admin/sla/create',
            CreateSlaHandler::class,
            ['GET', 'POST'],
            'admin.sla_create'
        );

        $app->route(
            '/admin/sla/edit/{id:\d}',
            EditSlaHandler::class,
            ['GET', 'POST'],
            'admin.sla_edit'
        );

        $app->get(
            '/admin/sla/list',
            ListSlaHandler::class,
            'admin.sla_list',
        );

        $app->get(
            '/admin/sla/view/{id:\d}',
            ViewSlaHandler::class,
            'admin.sla_view',
        );

        return $app;
    }
}
