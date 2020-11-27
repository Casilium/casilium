<?php

declare(strict_types=1);

namespace ServiceLevel;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
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
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'doctrine'     => $this->getDoctrineEntities(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class,
                ],
            ],
            'invokables' => [],
            'factories'  => [
                Handler\CreateBusinessHoursHandler::class => Handler\Factory\CreateBusinessHoursHandlerFactory::class,
                Handler\DeleteBusinessHoursHandler::class => Handler\Factory\DeleteBusinessHoursHandlerFactory::class,
                Handler\EditBusinessHoursHandler::class   => Handler\Factory\EditBusinessHoursHandlerFactory::class,
                Handler\ListBusinessHoursHandler::class   => Handler\Factory\ListBusinessHoursHandlerFactory::class,
                Handler\CreateSlaHandler::class           => Handler\Factory\CreateSlaHandlerFactory::class,
                Handler\EditSlaHandler::class             => Handler\Factory\EditSlaHandlerFactory::class,
                Handler\ListSlaHandler::class             => Handler\Factory\ListSlaHandlerFactory::class,
                Handler\ViewSlaHandler::class             => Handler\Factory\ViewSlaHandlerFactory::class,
                Hydrator\SlaHydrator::class               => Hydrator\Factory\SlaHydratorFactory::class,
                Service\SlaService::class                 => Service\Factory\SlaServiceFactory::class,
            ],
        ];
    }

    /**
     * Configure doctrine entities
     *
     * @return array
     */
    public function getDoctrineEntities(): array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'class'   => MappingDriverChain::class,
                    'drivers' => [
                        'ServiceLevel\Entity' => 'sla_entity',
                    ],
                ],
                'sla_entity'  => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'sla' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
