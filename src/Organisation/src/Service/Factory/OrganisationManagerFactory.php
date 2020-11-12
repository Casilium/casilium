<?php

declare(strict_types=1);

namespace Organisation\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Organisation\Service\OrganisationManager;

class OrganisationManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager = $container->get(EntityManager::class);
        return new OrganisationManager($entityManager);
    }
}