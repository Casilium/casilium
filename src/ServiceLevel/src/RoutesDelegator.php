<?php

declare(strict_types=1);

namespace ServiceLevel;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\AssignSlaHandler;
use ServiceLevel\Handler\CalculateDueHandler;
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
            'sla.create_business_hours'
        );

        $app->route(
            '/admin/sla/business-hours/edit/{id:\d}',
            EditBusinessHoursHandler::class,
            ['GET', 'POST'],
            'sla.edit_business_hours'
        );

        $app->get(
            '/admin/sla/business-hours/list',
            ListBusinessHoursHandler::class,
            'sla.list_business_hours'
        );

        $app->get(
            '/admin/sla/business-hours/delete/{id:\d}[/confirm/{confirm}]',
            DeleteBusinessHoursHandler::class,
            'sla.delete_business_hours'
        );

        $app->route(
            '/admin/sla/create',
            CreateSlaHandler::class,
            ['GET', 'POST'],
            'sla.create'
        );

        $app->route(
            '/admin/sla/edit/{id:\d}',
            EditSlaHandler::class,
            ['GET', 'POST'],
            'sla.edit'
        );

        $app->get(
            '/admin/sla/list',
            ListSlaHandler::class,
            'sla.list',
        );

        $app->get(
            '/admin/sla/view/{id:\d}',
            ViewSlaHandler::class,
            'sla.view',
        );

        $app->route(
            '/admin/sla/assign/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}[/]',
            AssignSlaHandler::class,
            ['GET', 'POST'],
            'sla.assign'
        );

        $app->get(
            // @codingStandardsIgnoreLine
            '/sla/due/org/{org_id:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}/priority/{priority:\d}/{type:response|resolve}',
            CalculateDueHandler::class,
            'sla.due',
        );

        return $app;
    }
}
