<?php
declare(strict_types=1);

namespace User\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use User\Service;

class RoleManagerFactory
{
    public function __invoke(ContainerInterface $container): Service\RoleManager
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        $rbacManager = $container->get(Service\RbacManager::class);

        return new Service\RoleManager($em, $rbacManager);
    }
}
