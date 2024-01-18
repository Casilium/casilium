<?php

declare(strict_types=1);

namespace User\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use User\Service\RbacManager;

class RbacManagerFactory
{
    public function __invoke(ContainerInterface $container): RbacManager
    {
        /** @var StorageInterface $cache */
        $cache = $container->get(StorageInterface::class);

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new RbacManager($cache, $em);
    }
}
