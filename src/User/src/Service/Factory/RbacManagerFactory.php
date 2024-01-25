<?php

declare(strict_types=1);

namespace User\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use User\Service\RbacManager;

class RbacManagerFactory
{
    public function __invoke(ContainerInterface $container): RbacManager
    {
        /** @var AdapterInterface $cache */
        $cache = $container->get(AdapterInterface::class);

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new RbacManager($cache, $em);
    }
}
