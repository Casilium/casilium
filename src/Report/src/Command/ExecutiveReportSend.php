<?php

declare(strict_types=1);

namespace Report\Command;

use Carbon\Carbon;
use MailService\Service\MailService;
use Monolog\Logger;
use Organisation\Entity\Organisation;
use Report\Service\PdfService;
use Report\Service\ReportService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function array_filter;
use function array_unique;
use function array_values;
use function count;
use function explode;
use function file_put_contents;
use function is_array;
use function preg_replace;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;

class ExecutiveReportSend extends Command
{
    private ReportService $reportService;
    private PdfService $pdfService;
    private MailService $mailService;
    private Logger $logger;

    /** @var array<string, mixed> */
    private array $reportConfig;

    /**
     * @param array<string, mixed> $reportConfig
     */
    public function __construct(
        ReportService $reportService,
        PdfService $pdfService,
        MailService $mailService,
        Logger $logger,
        array $reportConfig = []
    ) {
        parent::__construct();

        $this->reportService = $reportService;
        $this->pdfService    = $pdfService;
        $this->mailService   = $mailService;
        $this->logger        = $logger;
        $this->reportConfig  = $reportConfig;
    }

    public function configure(): void
    {
        $this->setName('report:executive')
            ->setDescription('Generate and email the executive report');

        $this->addOption('org', null, InputOption::VALUE_REQUIRED, 'Organisation UUID');
        $this->addOption('to', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Recipient email');
        $this->addOption('start', null, InputOption::VALUE_REQUIRED, 'Report start date (Y-m-d)');
        $this->addOption('end', null, InputOption::VALUE_REQUIRED, 'Report end date (Y-m-d)');
        $this->addOption('out', null, InputOption::VALUE_REQUIRED, 'Write PDF to this file path');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if (! $this->mailService->isEnabled()) {
                $output->writeln('<comment>Mail service disabled; skipping report send</comment>');
                $this->logger->info('Mail service disabled; skipping report send');
                return Command::SUCCESS;
            }

            $orgUuid = trim((string) $input->getOption('org'));
            if ($orgUuid === '') {
                $output->writeln('<error>Missing required --org option</error>');
                return Command::FAILURE;
            }

            $recipients = $this->parseRecipients($input->getOption('to'));
            if ($recipients === []) {
                $output->writeln('<error>Missing recipient(s); use --to</error>');
                return Command::FAILURE;
            }

            $organisation = $this->reportService->findOrganisationByUuid($orgUuid);
            if (! $organisation instanceof Organisation) {
                $output->writeln('<error>Organisation not found for provided UUID</error>');
                return Command::FAILURE;
            }

            $this->reportService->setOrganisation($organisation);

            $startOption = $input->getOption('start');
            $endOption   = $input->getOption('end');
            if ($startOption !== null || $endOption !== null) {
                if ($startOption === null || $endOption === null) {
                    $output->writeln('<error>Both --start and --end must be provided together</error>');
                    return Command::FAILURE;
                }

                $startDate = Carbon::parse((string) $startOption, 'UTC')->startOfDay();
                $endDate   = Carbon::parse((string) $endOption, 'UTC')->endOfDay();
                $this->reportService->setReportDate($startDate, $endDate);
            }

            $stats = $this->reportService->buildExecutiveStats();

            $unresolvedTickets = [];
            if (($this->reportConfig['include_unresolved'] ?? false) === true) {
                $limit             = (int) ($this->reportConfig['unresolved_limit'] ?? 20);
                $unresolvedTickets = $this->reportService->getUnresolvedTickets($limit);
            }

            $pdfContent = $this->pdfService->generateExecutiveReport(
                $stats,
                $organisation,
                $this->reportService->getStartDate(),
                $this->reportService->getEndDate(),
                $this->reportConfig,
                $unresolvedTickets
            );

            $attachmentPath = $this->writePdf($pdfContent, $input->getOption('out'), $organisation);

            $subject = $this->buildSubject($organisation);
            $body    = $this->mailService->prepareBody('report_mail::executive-report', [
                'organisation' => $organisation,
                'startDate'    => $this->reportService->getStartDate(),
                'endDate'      => $this->reportService->getEndDate(),
            ]);

            foreach ($recipients as $recipient) {
                $this->mailService->sendWithAttachment($recipient, $subject, $body, $attachmentPath);
            }

            if ($input->getOption('out') === null) {
                unlink($attachmentPath);
            }

            $output->writeln(sprintf('<info>Sent executive report to %d recipient(s)</info>', count($recipients)));
            $this->logger->info('Executive report sent', [
                'org_uuid'   => $orgUuid,
                'recipients' => $recipients,
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Fatal error: %s</error>', $e->getMessage()));
            $this->logger->critical('Executive report send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * @param array<int, string>|string|null $raw
     * @return array<int, string>
     */
    private function parseRecipients(array|string|null $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $values = is_array($raw) ? $raw : [$raw];
        $split  = [];
        foreach ($values as $value) {
            $parts = explode(',', (string) $value);
            foreach ($parts as $part) {
                $split[] = trim($part);
            }
        }

        return array_values(array_unique(array_filter($split)));
    }

    private function buildSubject(Organisation $organisation): string
    {
        $startDate = $this->reportService->getStartDate();
        $endDate   = $this->reportService->getEndDate();

        if ($startDate->format('m') === $endDate->format('m')) {
            $period = $startDate->format('F Y');
        } else {
            $period = sprintf('%s to %s', $startDate->format('F Y'), $endDate->format('F Y'));
        }

        return sprintf('Executive Report - %s (%s)', $organisation->getName(), $period);
    }

    private function writePdf(string $pdfContent, ?string $out, Organisation $organisation): string
    {
        $outputPath = null;
        if ($out !== null) {
            $outputPath = $out;
        }

        if ($outputPath === null || $outputPath === '') {
            $outputPath = $this->buildTempPath($organisation);
        }

        if (file_put_contents($outputPath, $pdfContent) === false) {
            throw new RuntimeException('Failed to write report PDF');
        }
        return $outputPath;
    }

    private function buildTempPath(Organisation $organisation): string
    {
        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '-', $organisation->getName());
        $temp     = tempnam(sys_get_temp_dir(), 'exec-report-');
        if ($temp === false) {
            throw new RuntimeException('Failed to create temporary file for report');
        }

        unlink($temp);

        return sprintf('%s-%s.pdf', $temp, $safeName);
    }
}
