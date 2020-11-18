<?php

declare(strict_types=1);

namespace OrganisationSite;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Mezzio\Application;
use OrganisationSite\Service\Factory\SiteManagerFactory;
use OrganisationSite\Service\SiteManager;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @returns array
     */
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'templates'     => $this->getTemplates(),
            'doctrine'      => $this->getDoctrineEntities(),
            'access_filter' => $this->getAccessFilter(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class,
                ],
            ],
            'factories'  => [
                SiteManager::class               => SiteManagerFactory::class,
                Handler\CreateSiteHandler::class => Handler\Factory\CreateSiteHandlerFactory::class,
                Handler\DeleteSiteHandler::class => Handler\Factory\DeleteSiteHandlerFactory::class,
                Handler\EditSiteHandler::class   => Handler\Factory\EditSiteHandlerFactory::class,
                Handler\ListSiteHandler::class   => Handler\Factory\ListSiteHandlerFactory::class,
                Handler\ViewSiteHandler::class   => Handler\Factory\ViewSiteHandlerFactory::class,
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
                'orm_default'              => [
                    'class'   => MappingDriverChain::class,
                    'drivers' => [
                        'OrganisationSite\Entity' => 'organisation_site_entity',
                    ],
                ],
                'organisation_site_entity' => [
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
                'organisation_site' => [
                    ['allow' => '@'],
                ],
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @returns array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'site' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
