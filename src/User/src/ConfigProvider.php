<?php

declare(strict_types=1);

namespace User;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Mezzio\Application;

/**
 * The configuration provider for the User module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'view_helpers' => $this->getViewHelperConfig(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
                Application::class => [
                    RouterDelegator::class,
                ],
            ],
            'invokables' => [
            ],
            'factories'  => [
                // Role Pages
                Handler\AddRolePageHandler::class => Handler\Factory\AddRolePageHandlerFactory::class,
                Handler\DeleteRolePageHandler::class => Handler\Factory\DeleteRolePageHandlerFactory::class,
                Handler\EditRolePageHandler::class => Handler\Factory\EditRolePageHandlerFactory::class,
                Handler\ListRolePageHandler::class => Handler\Factory\ListRolePageHandlerFactory::class,
                Handler\ViewRolePageHandler::class => Handler\Factory\ViewRolePageHandlerFactory::class,

                Handler\EditRolePermissionsPageHandler::class =>
                    Handler\Factory\EditRolePermissionsPageHandlerFactory::class,

                // Permission Pages
                Handler\AddPermissionPageHandler::class => Handler\Factory\AddPermissionPageFactory::class,
                Handler\DeletePermissionPageHandler::class => Handler\Factory\DeletePermissionPageFactory::class,
                Handler\EditPermissionPageHandler::class => Handler\Factory\EditPermissionPageFactory::class,
                Handler\ListPermissionPageHandler::class => Handler\Factory\ListPermissionPageFactory::class,
                Handler\ViewPermissionPageHandler::class => Handler\Factory\ViewPermissionPageHandlerFactory::class,

                Handler\AddUserPageHandler::class => Handler\Factory\AddUserPageFactory::class,
                Handler\EditUserPageHandler::class => Handler\Factory\EditUserPageFactory::class,
                Handler\ListUserPageHandler::class => Handler\Factory\ListUserPageFactory::class,
                Handler\ViewUserPageHandler::class => Handler\Factory\ViewUserPageHandlerFactory::class,

                Middleware\AuthorisationMiddleware::class => Middleware\Factory\AuthorisationMiddlewareFactory::class,

                Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
                Service\RbacManager::class => Service\Factory\RbacManagerFactory::class,
                Service\RoleManager::class => Service\Factory\RoleManagerFactory::class,
                Service\PermissionManager::class => Service\Factory\PermissionManagerFactory::class,
                Service\UserManager::class => Service\Factory\UserManagerFactory::class,

                EventListener\UserEventListener::class => EventListener\UserEventListener::class,
            ],
        ];
    }

    /**
     * Returns the view helper configuration
     */
    public function getViewHelperConfig(): array
    {
        return [
            'aliases' => [
                'GetUserNameFromId' => View\Helper\GetUserNameFromId::class,
            ],
            'factories' => [
                View\Helper\GetUserNameFromId::class => View\Helper\Factory\GetUserNameFromIdFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates() : array
    {
        return [
            'paths' => [
                'user'          => [__DIR__ . '/../templates/user'],
                'role'          => [__DIR__ . '/../templates/role'],
                'permission'    => [__DIR__ . '/../templates/permission'],
            ],
        ];
    }

    public function getDoctrineConfig() : array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                    'drivers' => [
                        'User\Entity' => 'user_entity',
                    ],
                ],
                'user_entity' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'dir'   => __DIR__ . '/Entity/',
                ],
            ],
        ];
    }
}
