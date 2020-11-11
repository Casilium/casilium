<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler;
use Mezzio\Template\TemplateRendererInterface;

class ListRolePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): Handler\ListRolePageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new Handler\ListRolePageHandler($em, $renderer);
    }
}
