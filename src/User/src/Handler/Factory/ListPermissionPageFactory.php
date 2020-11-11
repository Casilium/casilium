<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler\ListPermissionPageHandler;
use Mezzio\Template\TemplateRendererInterface;

class ListPermissionPageFactory
{
    public function __invoke(ContainerInterface $container): ListPermissionPageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $render */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new ListPermissionPageHandler($em, $renderer);
    }
}
