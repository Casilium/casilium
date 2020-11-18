<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Handler\ViewPermissionPageHandler;

class ViewPermissionPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): ViewPermissionPageHandler
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        $renderer = $container->get(TemplateRendererInterface::class);

        return new ViewPermissionPageHandler($em, $renderer);
    }
}
