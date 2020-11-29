<?php

declare(strict_types=1);

namespace Organisation\Service\Factory;

use Doctrine\ORM\EntityManager;
use Organisation\Service\ImportExportService;
use Psr\Container\ContainerInterface;

class ImportExportServiceFactory
{
    public function __invoke(ContainerInterface $container): ImportExportService
    {
        $entityManager = $container->get(EntityManager::class);
        return new ImportExportService($entityManager);
    }
}
