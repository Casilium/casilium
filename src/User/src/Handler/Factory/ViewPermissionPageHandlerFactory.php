<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler\ViewPermissionPageHandler;
use Mezzio\Template\TemplateRendererInterface;

class ViewPermissionPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): ViewPermissionPageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        $renderer = $container->get(TemplateRendererInterface::class);

        return new ViewPermissionPageHandler($em, $renderer);
    }
}
