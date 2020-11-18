<?php
declare(strict_types=1);

namespace User\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use User\Service\PermissionManager;
use User\Service\RbacManager;

class PermissionManagerFactory
{
    public function __invoke(ContainerInterface $container): PermissionManager
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var RbacManager $rbacManager */
        $rbacManager = $container->get(RbacManager::class);

        return new PermissionManager($em, $rbacManager);
    }
}
