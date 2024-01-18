<?php

declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Handler;
use User\Service\RoleManager;

class ViewRolePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\ViewRolePageHandler
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var RoleManager $roleManager */
        $roleManager = $container->get(RoleManager::class);

        return new Handler\ViewRolePageHandler($em, $roleManager, $renderer);
    }
}
