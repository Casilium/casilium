<?php
declare(strict_types=1);

namespace UserAuthentication\View\Helper\Factory;

use UserAuthentication\View\Helper\IdentityViewHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class IdentityViewHelperFactory
{
    public function __invoke(ContainerInterface $container): IdentityViewHelper
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        return new IdentityViewHelper($entityManager);
    }
}
