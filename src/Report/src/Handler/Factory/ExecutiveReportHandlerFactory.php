<?php

declare(strict_types=1);

namespace Report\Handler\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Report\Handler\ExecutiveReportHandler;
use Report\Service\ReportService;

class ExecutiveReportHandlerFactory
{
    public function __invoke(ContainerInterface $container): ExecutiveReportHandler
    {
        $renderer      = $container->get(TemplateRendererInterface::class);
        $reportService = $container->get(ReportService::class);
        return new ExecutiveReportHandler($reportService, $renderer);
    }
}
