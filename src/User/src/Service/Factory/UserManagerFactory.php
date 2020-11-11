<?php
declare(strict_types=1);

namespace User\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use User\Service;
use Mezzio\Template\TemplateRendererInterface;

class UserManagerFactory
{
    public function __invoke(ContainerInterface $container): Service\UserManager
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var Service\RoleManager $roleManager */
        $roleManager = $container->get(Service\RoleManager::class);

        /** @var Service\PermissionManager $permissionManager */
        $permissionManager = $container->get(Service\PermissionManager::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var array $config */
        $config = $container->get('config');

        return new Service\UserManager(
            $em,
            $roleManager,
            $permissionManager,
            $renderer,
            $config
        );
    }
}
