<?php

declare(strict_types=1);

namespace User\Command\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use User\Command\CreateUser;

class CreateUserFactory
{
    public function __invoke(ContainerInterface $container): CreateUser
    {
        return new CreateUser($container->get(EntityManagerInterface::class));
    }
}
