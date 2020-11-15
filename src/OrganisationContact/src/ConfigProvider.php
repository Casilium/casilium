<?php

declare(strict_types=1);

namespace OrganisationContact;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Mezzio\Application;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'access_filter' => $this->getAccessFilter(),
            'doctrine' => $this->getDoctrineConfiguration(),
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
                    RouteDelegator::class,
                ],
            ],
            'invokables' => [
            ],
            'factories'  => [
                Handler\CreateContactHandler::class => Handler\Factory\CreateContactHandlerFactory::class,
                Handler\ListContactHandler::class => Handler\Factory\ListContactHandlerFactory::class,
                Service\ContactService::class => Service\Factory\ContactServiceFactory::class,
            ],
        ];
    }

    /**
     * Doctrine configuration
     *
     * @return array
     */
    public function getDoctrineConfiguration() : array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                    'drivers' => [
                        'OrganisationContact\Entity' => 'organisation_contact',
                    ],
                ],
                'organisation_contact' => [
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
    public function getAccessFilter() : array
    {
        return [
            'routes' => [
                'contact' => [
                    ['allow' => '@']
                ],
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
                'contact' => [
                    __DIR__ . '/../templates/'
                ],
            ],
        ];
    }
}
