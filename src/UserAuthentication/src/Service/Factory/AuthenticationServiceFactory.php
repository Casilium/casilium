<?php

declare(strict_types=1);

namespace UserAuthentication\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use UserAuthentication\Service\AuthenticationService;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        $connection = $container->get(EntityManagerInterface::class)->getConnection();
        return new AuthenticationService($connection);
    }
}
