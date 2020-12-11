<?php

declare(strict_types=1);

namespace SlackIntegration;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'delegators' => [],
            'invokables' => [],
            'factories'  => [
                Service\Client::class => Service\Factory\ClientFactory::class,
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
