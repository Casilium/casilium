<?php

declare(strict_types=1);

namespace SlackIntegration\Service\Factory;

use Psr\Container\ContainerInterface;
use SlackIntegration\Service\Client;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $config = $container->get('config')['slack'] ?? [];

        return new Client($config);
    }
}
