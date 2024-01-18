<?php

declare(strict_types=1);

namespace UserAuthentication\View\Helper\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use UserAuthentication\View\Helper\IdentityViewHelper;

class IdentityViewHelperFactory
{
    public function __invoke(ContainerInterface $container): IdentityViewHelper
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        return new IdentityViewHelper($entityManager);
    }
}
