<?php

declare(strict_types=1);

namespace Ticket\Service\Factory;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Ticket\Service\QueueManager;

class QueueManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config        = $container->get('config')['encryption'] ?? [];
        $entityManager = $container->get(EntityManager::class);
        return new QueueManager($entityManager, $config);
    }
}
