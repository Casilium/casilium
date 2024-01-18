<?php

declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Handler\EditRolePermissionsPageHandler;
use User\Service\RoleManager;

class EditRolePermissionsPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): EditRolePermissionsPageHandler
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var RoleManager $roleManager */
        $roleManager = $container->get(RoleManager::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $helper */
        $helper = $container->get(UrlHelper::class);

        return new EditRolePermissionsPageHandler($em, $roleManager, $renderer, $helper);
    }
}
