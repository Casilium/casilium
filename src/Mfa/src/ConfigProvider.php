<?php

declare(strict_types=1);

namespace Mfa;

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
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
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
                Handler\EnableMfaHandler::class => Handler\Factory\EnableMfaHandlerFactory::class,
                Handler\ValidateMfaHandler::class => Handler\Factory\ValidateMfaHandlerFactory::class,
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
                'mfa'    => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
