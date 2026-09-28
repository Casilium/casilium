<?php

declare(strict_types=1);

namespace Ticket\Service\Factory;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Ticket\Service\CannedResponseManager;

class CannedResponseManagerFactory
{
    public function __invoke(ContainerInterface $container): CannedResponseManager
    {
        return new CannedResponseManager($container->get(EntityManager::class));
    }
}
