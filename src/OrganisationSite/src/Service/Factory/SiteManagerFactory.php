<?php

declare(strict_types=1);

namespace OrganisationSite\Service\Factory;

use Doctrine\ORM\EntityManager;
use OrganisationSite\Service\SiteManager;
use Psr\Container\ContainerInterface;

class SiteManagerFactory
{
    /**
     * Factory for SiteManager
     *
     * @return SiteManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $entityManager = $container->get(EntityManager::class);
        return new SiteManager($entityManager);
    }
}
