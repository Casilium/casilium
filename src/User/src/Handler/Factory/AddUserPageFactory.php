<?php
declare(strict_types=1);

namespace User\Handler\Factory;

use Psr\Container\ContainerInterface;
use User\Handler;
use User\Service\UserManager;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;

class AddUserPageFactory
{
    public function __invoke(ContainerInterface $container): Handler\AddUserPageHandler
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var UserManager $userManager */
        $userManager = $container->get(UserManager::class);

        /** @var TemplateRendererInterface $renderer */
        $renderer = $container->get(TemplateRendererInterface::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get(UrlHelper::class);

        return new Handler\AddUserPageHandler($em, $userManager, $renderer, $urlHelper);
    }
}
