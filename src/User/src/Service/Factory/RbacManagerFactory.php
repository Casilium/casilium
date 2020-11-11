<?php
declare(strict_types=1);

namespace User\Service\Factory;

use User\Service\RbacManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;

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
