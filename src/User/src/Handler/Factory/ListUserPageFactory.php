<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Handler;

class ListUserPageFactory
{
    public function __invoke(ContainerInterface $container): Handler\ListUserPageHandler
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        return new Handler\ListUserPageHandler($em, $renderer);
    }
}
