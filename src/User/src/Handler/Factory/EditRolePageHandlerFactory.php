<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Handler;
use User\Service;

class EditRolePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\EditRolePageHandler
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var Service\RoleManager $roleManager */
        $roleManager = $container->get(Service\RoleManager::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        $urlHelper = $container->get(UrlHelper::class);

        return new Handler\EditRolePageHandler($em, $roleManager, $renderer, $urlHelper);
    }
}
