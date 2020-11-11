<?php
declare(strict_types=1);

namespace User\Service\Factory;

use User\Service\AuthManager;
use User\Service\RbacManager;
use Psr\Container\ContainerInterface;

class AuthManagerFactory
{
    public function __invoke(ContainerInterface $container): AuthManager
    {
        $config = $container->get('config')['access_filter'] ?? [];
        $rbacManager = $container->get(RbacManager::class);

        return new AuthManager($rbacManager, $config);
    }
}
