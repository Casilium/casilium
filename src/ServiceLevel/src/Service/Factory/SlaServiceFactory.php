<?php

declare(strict_types=1);

namespace ServiceLevel\Service\Factory;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use ServiceLevel\Service\SlaService;

class SlaServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new SlaService($container->get(EntityManager::class));
    }
}