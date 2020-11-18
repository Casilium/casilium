<?php
declare(strict_types=1);

namespace User\Service\Factory;

use Psr\Container\ContainerInterface;
use User\Service\AuthManager;
use User\Service\RbacManager;

class AuthManagerFactory
{
    public function __invoke(ContainerInterface $container): AuthManager
    {
        $config      = $container->get('config')['access_filter'] ?? [];
        $rbacManager = $container->get(RbacManager::class);

        return new AuthManager($rbacManager, $config);
    }
}
