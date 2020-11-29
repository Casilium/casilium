<?php

declare(strict_types=1);

namespace ServiceLevel\Hydrator\Factory;

use Psr\Container\ContainerInterface;
use ServiceLevel\Hydrator\SlaHydrator;
use ServiceLevel\Service\SlaService;

class SlaHydratorFactory
{
    public function __invoke(ContainerInterface $container): SlaHydrator
    {
        $service = $container->get(SlaService::class);
        return new SlaHydrator($service);
    }
}
