<?php

declare(strict_types=1);

namespace Mfa\Service\Factory;

use Doctrine\ORM\EntityManager;
use Mfa\Service\MfaService;
use Psr\Container\ContainerInterface;

class MfaServiceFactory
{
    public function __invoke(ContainerInterface $container): MfaService
    {
        $connection = $container->get(EntityManager::class)->getConnection();
        $config     = $container->get('config')['mfa'] ?? [];
        return new MfaService($connection, $config);
    }
}
