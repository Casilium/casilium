<?php

declare(strict_types=1);

namespace Report\Handler\Factory;

use Psr\Container\ContainerInterface;
use Report\Handler\ExecutiveReportHandler;
use Report\Service\PdfService;
use Report\Service\ReportService;

class ExecutiveReportHandlerFactory
{
    public function __invoke(ContainerInterface $container): ExecutiveReportHandler
    {
        $reportService = $container->get(ReportService::class);
        $pdfService    = $container->get(PdfService::class);
        return new ExecutiveReportHandler($reportService, $pdfService);
    }
}
