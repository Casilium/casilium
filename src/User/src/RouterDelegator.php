<?php

declare(strict_types=1);

namespace User;

use Psr\Container\ContainerInterface;
use Mezzio\Application;

class RouterDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();

        // Role :: Add
        $app->route(
            '/admin/role/add',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\AddRolePageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.role.add'
        );

        // Role :: List
        $app->get(
            '/admin/role/list',
            Handler\ListRolePageHandler::class,
            'admin.role.list'
        );

        $app->get(
            '/admin/role/delete/{id}',
            Handler\DeleteRolePageHandler::class,
            'admin.role.delete'
        );

        $app->route(
            '/admin/role/edit/{id}',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\EditRolePageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.role.edit'
        );

        $app->get(
            '/admin/role/view/{id}',
            Handler\ViewRolePageHandler::class,
            'admin.role-view'
        );

        // permission :: add
        $app->route(
            '/admin/permission/add',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\AddPermissionPageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.permission.create'
        );


        // Permission :: List
        $app->get(
            '/admin/permission/list',
            Handler\ListPermissionPageHandler::class,
            'admin.permission.list'
        );

        $app->get(
            '/admin/permission/delete/{id}',
            Handler\DeletePermissionPageHandler::class,
            'admin.permission.delete'
        );

        $app->route(
            '/admin/permission/edit/{id}',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\EditPermissionPageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.permission.edit'
        );

        $app->get(
            '/admin/permission/view/{id}',
            Handler\ViewPermissionPageHandler::class,
            'admin.permission.view'
        );

        $app->route(
            '/admin/role/{id}/edit-permission',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\EditRolePermissionsPageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.role.edit-permission'
        );

        // user rotes
        $app->route(
            '/admin/user/add',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\AddUserPageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.user.create'
        );

        // user rotes
        $app->route(
            '/admin/user/edit/{id}',
            [
                \Mezzio\Csrf\CsrfMiddleware::class,
                Handler\EditUserPageHandler::class,
            ],
            ['GET', 'POST'],
            'admin.user.edit'
        );

        $app->get(
            '/admin/user/list[/]',
            Handler\ListUserPageHandler::class,
            'admin.user.list'
        );

        $app->get(
            '/admin/user/view/{id}',
            Handler\ViewUserPageHandler::class,
            'admin.user.view'
        );

        return $app;
    }
}