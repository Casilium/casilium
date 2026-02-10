<?php

declare(strict_types=1);

namespace Report\Command\Factory;

use Logger\Service\LogService;
use MailService\Service\MailService;
use Psr\Container\ContainerInterface;
use Report\Command\ExecutiveReportSend;
use Report\Service\PdfService;
use Report\Service\ReportService;

class ExecutiveReportSendFactory
{
    public function __invoke(ContainerInterface $container): ExecutiveReportSend
    {
        $reportService = $container->get(ReportService::class);
        $pdfService    = $container->get(PdfService::class);
        $mailService   = $container->get(MailService::class);
        $logger        = $container->get(LogService::class);
        $config        = $container->get('config');
        $reportConfig  = $config['tickets']['reports']['executive'] ?? [];

        return new ExecutiveReportSend(
            $reportService,
            $pdfService,
            $mailService,
            $logger,
            $reportConfig
        );
    }
}
