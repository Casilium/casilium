<?php

declare(strict_types=1);

namespace Ticket;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;


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
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'doctrine' => $this->getDoctrineEntities(),
            'access_filter' => $this->getAccessFilter(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
                \Mezzio\Application::class => [
                    RoutesDelegator::class,
                ],
            ],
            'factories' => [
                Handler\CreateTicketHandler::class => Handler\Factory\TicketCreateHandlerFactory::class,
                Handler\ListTicketHandler::class => Handler\Factory\ListTickerHandlerFactory::class,
                Hydrator\TicketHydrator::class => Hydrator\Factory\TicketHydratorFactory::class,
                Service\TicketService::class => Service\Factory\TicketServiceFactory::class,
                Service\QueueManager::class => Service\Factory\QueueManagerFactory::class,
            ],
        ];
    }

    /**
     * Configure doctrine entities
     * @return array
     */
    public function getDoctrineEntities() : array
    {
        return [
            'driver' => [
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                    'drivers' => [
                        'Ticket\Entity' => 'ticket_entity',
                    ],
                ],
                'ticket_entity' => [
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
                'ticket' => [
                    ['allow' => '@']
                ],
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @returns array
     */
    public function getTemplates() : array
    {
        return [
            'paths' => [
                'ticket'    => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}