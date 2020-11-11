<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler\DeletePermissionPageHandler;
use User\Service\PermissionManager;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class DeletePermissionPageFactory
{
    public function __invoke(ContainerInterface $container): DeletePermissionPageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var PermissionManager $permissionManager */
        $permissionManager = $container->get(PermissionManager::class);

        /** @var TemplateRendererInterface $render */
        $render = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $helper */
        $helper = $container->get(UrlHelper::class);

        return new DeletePermissionPageHandler($em, $permissionManager, $render, $helper);
    }
}
