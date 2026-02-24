<?php

declare(strict_types=1);

namespace Api;

use Mezzio\Application;

class ConfigProvider
{
    /**
     * @return array<string,array>
     */
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'access_filter' => $this->getAccessFilter(),
        ];
    }

    /**
     * @return array<string,array<string,array<int,array<string,string>>>>
     */
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

    /**
     * @return array<string,array>
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
                Handler\OrganisationContactsHandler::class => Handler\Factory\OrganisationContactsHandlerFactory::class,
                Handler\TicketSearchHandler::class         => Handler\Factory\TicketSearchHandlerFactory::class,
            ],
        ];
    }
}
