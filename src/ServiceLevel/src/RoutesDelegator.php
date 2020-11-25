<?php
declare(strict_types=1);

namespace ServiceLevel;

use Mezzio\Application;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\CreateBusinessHoursHandler;
use ServiceLevel\Handler\EditBusinessHoursHandler;
use ServiceLevel\Handler\ListBusinessHoursHandler;

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
            '/admin/sla/business-hours/edit/{id}',
            EditBusinessHoursHandler::class,
            ['GET', 'POST'],
            'admin.sla_edit_business_hours'
        );

        $app->get(
            '/admin/sla/business-hours/list',
            ListBusinessHoursHandler::class,
            'admin.sla_list_business_hours'
        );

        return $app;
    }
}
