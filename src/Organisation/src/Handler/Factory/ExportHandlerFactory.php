<?php

declare(strict_types=1);

namespace Organisation\Handler\Factory;

use Organisation\Handler\ExportHandler;
use Organisation\Service\ImportExportService;
use Psr\Container\ContainerInterface;

class ExportHandlerFactory
{
    public function __invoke(ContainerInterface $container): ExportHandler
    {
        $service = $container->get(ImportExportService::class);
        return new ExportHandler($service);
    }
}
