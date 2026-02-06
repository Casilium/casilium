<?php

declare(strict_types=1);

namespace Ticket\Command;

use Carbon\Carbon;
use MailService\Service\MailService;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;

use function gmdate;
use function sprintf;
use function strtolower;

class Notifications extends Command
{
    /** @var TicketService */
    protected $ticketService;

    /** @var MailService */
    protected $mailService;

    /** @var Logger */
    protected $logger;

    public function __construct(TicketService $service, MailService $mailService, Logger $logger)
    {
        parent::__construct();

        $this->ticketService = $service;
        $this->mailService   = $mailService;
        $this->logger        = $logger;
    }

    /**
     * Configure this command
     */
    public function configure(): void
    {
        $this->setName('ticket:notifications')
            ->setDescription('Send ticket notifications');

        $this->addArgument('target', InputArgument::REQUIRED, 'Target time (ie 4)');
        $this->addArgument('period', InputArgument::REQUIRED, 'minutes|hours|days');
    }

    /**
     * @param InputInterface $input input interface
     * @param OutputInterface $output output interface
     * @return int exit code
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $target         = (int) $input->getArgument('target');
            $periodArgument = strtolower($input->getArgument('period'));

            $targetType = $this->validatePeriod($periodArgument);
            if ($targetType === null) {
                $output->writeln('<error>Invalid argument, use minutes|hours|days|weeks|months</error>');
                $this->logger->warning('Invalid period argument', ['period' => $periodArgument]);
                return Command::FAILURE;
            }

            $this->logger->info('Starting ticket notification process', [
                'target' => $target,
                'period' => $periodArgument,
            ]);

            $successCount = 0;
            $skippedCount = 0;
            $failCount    = 0;

            // Process due tickets
            $dueCounts     = $this->processDueTickets($target, $targetType, $output);
            $successCount += $dueCounts['success'];
            $skippedCount += $dueCounts['skipped'];
            $failCount    += $dueCounts['failed'];

            // Process overdue tickets
            $overdueCounts = $this->processOverdueTickets($output);
            $successCount += $overdueCounts['success'];
            $skippedCount += $overdueCounts['skipped'];
            $failCount    += $overdueCounts['failed'];

            $output->writeln(sprintf(
                '<info>Sent %d notifications, %d skipped (already notified), %d failed</info>',
                $successCount,
                $skippedCount,
                $failCount
            ));

            $this->logger->info('Notification process completed', [
                'success' => $successCount,
                'skipped' => $skippedCount,
                'failed'  => $failCount,
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Fatal error: %s</error>', $e->getMessage()));
            $this->logger->critical('Notification process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Validate and convert period argument to constant
     */
    private function validatePeriod(string $period): ?int
    {
        return match ($period) {
            'minutes' => TicketService::DUE_PERIOD_MINUTES,
            'hours'   => TicketService::DUE_PERIOD_HOURS,
            'days'    => TicketService::DUE_PERIOD_DAYS,
            'weeks'   => TicketService::DUE_PERIOD_WEEKS,
            'months'  => TicketService::DUE_PERIOD_MONTHS,
            default   => null,
        };
    }

    /**
     * Process tickets that are due within target period
     */
    private function processDueTickets(int $target, int $targetType, OutputInterface $output): array
    {
        $successCount = 0;
        $skippedCount = 0;
        $failCount    = 0;

        try {
            /** @var Ticket[] $tickets */
            $tickets = $this->ticketService->findTicketsDueWithin($target, $targetType);

            if (empty($tickets)) {
                $output->writeln('<comment>No tickets due within target period</comment>');
                return ['success' => 0, 'skipped' => 0, 'failed' => 0];
            }

            foreach ($tickets as $ticket) {
                try {
                    $now     = Carbon::now('UTC');
                    $due     = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate());
                    $seconds = $now->diffInSeconds($due);

                    $wasSent = $this->ticketService->sendNotificationEmail($ticket, $target, $targetType);

                    if ($wasSent) {
                        $output->writeln(sprintf(
                            '<info>Sent notification for ticket #%s (due in %s)</info>',
                            $ticket->getId(),
                            gmdate('H:i:s', $seconds)
                        ));

                        $successCount++;
                        $this->logger->info('Due ticket notification sent', [
                            'ticket_id' => $ticket->getId(),
                            'due_in'    => gmdate('H:i:s', $seconds),
                        ]);
                    } else {
                        $output->writeln(sprintf(
                            '<comment>Skipped ticket #%s (already notified)</comment>',
                            $ticket->getId()
                        ));

                        $skippedCount++;
                        $this->logger->info('Due ticket notification skipped (already notified)', [
                            'ticket_id' => $ticket->getId(),
                        ]);
                    }
                } catch (Throwable $e) {
                    $failCount++;
                    $output->writeln(sprintf(
                        '<error>Failed to send notification for ticket #%s: %s</error>',
                        $ticket->getId(),
                        $e->getMessage()
                    ));
                    $this->logger->error('Due ticket notification failed', [
                        'ticket_id' => $ticket->getId(),
                        'error'     => $e->getMessage(),
                    ]);
                    // Continue processing other tickets
                }
            }
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch due tickets', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return ['success' => $successCount, 'skipped' => $skippedCount, 'failed' => $failCount];
    }

    /**
     * Process tickets that are now overdue
     */
    private function processOverdueTickets(OutputInterface $output): array
    {
        $successCount = 0;
        $failCount    = 0;

        try {
            $tickets = $this->ticketService->getEntityManager()
                ->getRepository(Ticket::class)
                ->findOverdueTickets();

            if (empty($tickets)) {
                $output->writeln('<comment>No overdue tickets</comment>');
                return ['success' => 0, 'skipped' => 0, 'failed' => 0];
            }

            foreach ($tickets as $ticket) {
                try {
                    $wasSent = $this->ticketService->sendOverdueNotificationEmail($ticket);

                    if ($wasSent) {
                        $output->writeln(sprintf(
                            '<info>Sent overdue notification for ticket #%s</info>',
                            $ticket->getId()
                        ));

                        $successCount++;
                        $this->logger->info('Overdue ticket notification sent', [
                            'ticket_id' => $ticket->getId(),
                        ]);
                    }
                } catch (Throwable $e) {
                    $failCount++;
                    $output->writeln(sprintf(
                        '<error>Failed to send overdue notification for ticket #%s: %s</error>',
                        $ticket->getId(),
                        $e->getMessage()
                    ));
                    $this->logger->error('Overdue ticket notification failed', [
                        'ticket_id' => $ticket->getId(),
                        'error'     => $e->getMessage(),
                    ]);
                    // Continue processing other tickets
                }
            }
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch overdue tickets', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return ['success' => $successCount, 'skipped' => 0, 'failed' => $failCount];
    }
}
