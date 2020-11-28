<?php

declare(strict_types=1);

namespace ServiceLevel\Handler\Factory;

use Organisation\Service\OrganisationManager;
use Psr\Container\ContainerInterface;
use ServiceLevel\Handler\CalculateDueHandler;

class CalculateDueHandlerFactory
{
    public function __invoke(ContainerInterface $container): CalculateDueHandler
    {
        $organisationManager = $container->get(OrganisationManager::class);
        return new CalculateDueHandler($organisationManager);
    }
}