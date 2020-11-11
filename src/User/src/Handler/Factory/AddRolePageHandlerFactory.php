<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler;
use User\Service;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class AddRolePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\AddRolePageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var Service\RoleManager $rolemanager */
        $rolemanager = $container->get(Service\RoleManager::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        $urlHelper = $container->get(UrlHelper::class);

        return new Handler\AddRolePageHandler($em, $rolemanager, $renderer, $urlHelper);
    }
}
