<?php

declare(strict_types=1);

namespace Organisation\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Organisation\Service\OrganisationManager;
use OrganisationSite\Service\SiteManager;

class OrganisationManagerFactory
{
    public function __invoke(ContainerInterface $container): OrganisationManager
    {
        $entityManager = $container->get(EntityManager::class);
        $siteManager   = $container->get(SiteManager::class);
        return new OrganisationManager($entityManager, $siteManager);
    }
}
