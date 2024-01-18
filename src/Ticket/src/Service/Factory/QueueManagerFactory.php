<?php

declare(strict_types=1);

namespace Ticket\Service\Factory;

use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Ticket\Service\QueueManager;

class QueueManagerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): QueueManager
    {
        $config        = $container->get('config')['encryption'] ?? [];
        $entityManager = $container->get(EntityManager::class);
        return new QueueManager($entityManager, $config);
    }
}
