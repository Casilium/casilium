<?php
declare(strict_types=1);

namespace User\Service\Factory;

use User\Service\RbacManager;
use User\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

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
