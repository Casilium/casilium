<?php

declare(strict_types=1);

namespace Api;

use Mezzio\Application;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'access_filter' => $this->getAccessFilter(),
        ];
    }

    public function getAccessFilter(): array
    {
        return [
            'routes' => [
                'api' => [
                    ['allow' => '@'],
                ],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class,
                ],
            ],
            'factories'  => [
                Handler\OrganisationContactsHandler::class => Handler\Factory\OrganisationContactsHandlerFactory::class,
                Handler\TicketSearchHandler::class         => Handler\Factory\TicketSearchHandlerFactory::class,
            ],
        ];
    }
}
