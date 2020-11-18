<?php

declare(strict_types=1);

namespace OrganisationContact\Service\Factory;

use Doctrine\ORM\EntityManager;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Service\ContactService;
use Psr\Container\ContainerInterface;

class ContactServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager       = $container->get(EntityManager::class);
        $organisationService = $container->get(OrganisationManager::class);

        return new ContactService($entityManager, $organisationService);
    }
}
