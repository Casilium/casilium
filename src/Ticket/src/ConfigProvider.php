<?php

declare(strict_types=1);

namespace Ticket;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Mezzio\Application;

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
            'console'       => $this->getConsoleCommands(),
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
                Command\CreateTicketsFromEmail::class    => Command\Factory\CreateTicketsFromEmailFactory::class,
                Command\CloseResolvedTickets::class      => Command\Factory\CloseResolvedTicketsFactory::class,
                Command\Notifications::class             => Command\Factory\NotificationsFactory::class,
                Command\UpdateWaitingTickets::class      => Command\Factory\UpdatingWaitingTicketsFactory::class,
                EventListener\TicketEventListener::class => EventListener\TicketEventListener::class,
                Handler\AssignQueueMembersHandler::class => Handler\Factory\AssignQueueMembersHandlerFactory::class,
                Handler\CreateQueueHandler::class        => Handler\Factory\CreateQueueHandlerFactory::class,
                Handler\DeleteQueueHandler::class        => Handler\Factory\DeleteQueueHandlerFactory::class,
                Handler\EditQueueHandler::class          => Handler\Factory\EditQueueHandlerFactory::class,
                Handler\ListQueueHandler::class          => Handler\Factory\ListQueueHandlerFactory::class,
                Handler\CreateTicketHandler::class       => Handler\Factory\TicketCreateHandlerFactory::class,
                Handler\EditTickerHandler::class         => Handler\Factory\TicketEditHandlerFactory::class,
                Handler\ListTicketHandler::class         => Handler\Factory\ListTickerHandlerFactory::class,
                Handler\ViewTicketHandler::class         => Handler\Factory\ViewTicketHandlerFactory::class,
                Handler\GoToTicketHandler::class         => Handler\Factory\GoToTicketHandlerFactory::class,
                Hydrator\TicketHydrator::class           => Hydrator\Factory\TicketHydratorFactory::class,
                Service\TicketService::class             => Service\Factory\TicketServiceFactory::class,
                Service\QueueManager::class              => Service\Factory\QueueManagerFactory::class,
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
                'orm_default'   => [
                    'class'   => MappingDriverChain::class,
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
    public function getAccessFilter(): array
    {
        return [
            'routes' => [
                'ticket' => [
                    ['allow' => '+ticket.manage'],
                ],
            ],
        ];
    }

    public function getConsoleCommands(): array
    {
        return [
            'commands' => [
                Command\CreateTicketsFromEmail::class,
                Command\CloseResolvedTickets::class,
                Command\Notifications::class,
                Command\UpdateWaitingTickets::class,
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
                'ticket'      => [__DIR__ . '/../templates/'],
                'ticket_mail' => [__DIR__ . '/../templates/mail'],
            ],
        ];
    }
}
