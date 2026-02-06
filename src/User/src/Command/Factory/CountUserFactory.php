<?php

declare(strict_types=1);

namespace User\Command\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use User\Command\CountUser;

class CountUserFactory
{
    public function __invoke(ContainerInterface $container): CountUser
    {
        return new CountUser($container->get(EntityManagerInterface::class));
    }
}
