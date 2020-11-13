<?php

declare(strict_types=1);

namespace Account;

use Mezzio\Application;

/**
 * The configuration provider for the Mfa module
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
            'dependencies'  => $this->getDependencies(),
            'templates'     => $this->getTemplates(),
            'access_filter' => $this->getAccessFilter(),
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
                Handler\AccountPageHandler::class => Handler\Factory\AccountPageHandlerFactory::class,
                Handler\ChangePasswordHandler::class => Handler\Factory\ChangePasswordHandlerFactory::class,
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
                'account'    => [__DIR__ . '/../templates/'],
            ],
        ];
    }

    public function getAccessFilter() : array
    {
        return [
            'routes' => [
                'account' => [
                    ['allow' => '@']
                ],
            ],
        ];
    }
}
