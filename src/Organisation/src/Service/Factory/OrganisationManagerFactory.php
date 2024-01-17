<?php

declare(strict_types=1);

namespace Organisation\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use interop\container\containerinterface;
use Organisation\Service\OrganisationManager;
use OrganisationSite\Service\SiteManager;

class OrganisationManagerFactory
{
    public function __invoke(containerinterface $container): OrganisationManager
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        $siteManager   = $container->get(SiteManager::class);
        return new OrganisationManager($entityManager, $siteManager);
    }
}
