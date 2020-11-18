<?php

declare(strict_types=1);

namespace Organisation;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Mezzio\Application;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'templates'     => $this->getTemplates(),
            'access_filter' => $this->getAccessFilter(),
            'doctrine'      => $this->getDoctrineConfiguration(),
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
                    RouteDelegator::class,
                ],
            ],
            'invokables' => [],
            'factories'  => [
                Handler\OrganisationCreateHandler::class => Handler\Factory\OrganisationCreateHandlerFactory::class,
                Handler\OrganisationEditHandler::class   => Handler\Factory\OrganisationEditHandlerFactory::class,
                Handler\OrganisationListHandler::class   => Handler\Factory\OrganisationListHandlerFactory::class,
                Handler\OrganisationReadHandler::class   => Handler\Factory\OrganisationReadHandlerFactory::class,
                Handler\OrganisationDeleteHandler::class => Handler\Factory\OrganisationDeleteHandlerFactory::class,
                Service\OrganisationManager::class       => Service\Factory\OrganisationManagerFactory::class,
            ],
        ];
    }

    /**
     * Doctrine configuration
     *
     * @return array
     */
    public function getDoctrineConfiguration(): array
    {
        return [
            'driver' => [
                'orm_default'         => [
                    'class'   => MappingDriverChain::class,
                    'drivers' => [
                        'Organisation\Entity' => 'organisation_entity',
                    ],
                ],
                'organisation_entity' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }

    /**
     * Define access filter for accessing organisation
     *
     * @return array
     */
    public function getAccessFilter(): array
    {
        return [
            'routes' => [
                'organisation' => [
                    ['allow' => '@'],
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
                'organisation' => [
                    __DIR__ . '/../templates/',
                ],
            ],
        ];
    }
}
