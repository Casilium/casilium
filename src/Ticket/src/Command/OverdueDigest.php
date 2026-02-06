<?php

declare(strict_types=1);

namespace Ticket\Command;

use Carbon\Carbon;
use MailService\Service\MailService;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

use function count;
use function implode;
use function sprintf;

class OverdueDigest extends Command
{
    protected TicketService $ticketService;
    protected MailService $mailService;
    protected Logger $logger;

    public function __construct(TicketService $service, MailService $mailService, Logger $logger)
    {
        parent::__construct();

        $this->ticketService = $service;
        $this->mailService   = $mailService;
        $this->logger        = $logger;
    }

    public function configure(): void
    {
        $this->setName('ticket:overdue-digest')
            ->setDescription('Send overdue ticket digest');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $repository = $this->ticketService->getEntityManager()->getRepository(Ticket::class);
            if (! $repository instanceof TicketRepository) {
                $output->writeln('<error>Ticket repository not available</error>');
                return Command::FAILURE;
            }

            $tickets = $repository->findOverdueTicketsForDigest();
            if (empty($tickets)) {
                $output->writeln('<comment>No overdue tickets</comment>');
                return Command::SUCCESS;
            }

            $queues = [];
            foreach ($tickets as $ticket) {
                $queue     = $ticket->getQueue();
                $queueId   = $queue ? $queue->getId() : 0;
                $queueName = $queue ? $queue->getName() : 'Unassigned';

                if (! isset($queues[$queueId])) {
                    $queues[$queueId] = [
                        'queue'   => $queue,
                        'name'    => $queueName,
                        'tickets' => [],
                    ];
                }

                $due       = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate(), 'UTC');
                $overdueBy = $this->formatDuration($due, Carbon::now('UTC'));

                $assignedAgent = $ticket->getAssignedAgent();
                $assignedTo    = $assignedAgent ? $assignedAgent->getFullName() : 'Unassigned';

                $status     = $ticket->getStatus();
                $statusName = $status ? $status->getDescription() : 'Unknown';

                $contact     = $ticket->getContact();
                $contactName = $contact
                    ? $contact->getFirstName() . ' ' . $contact->getLastName()
                    : 'Unknown';

                $organisation     = $ticket->getOrganisation();
                $organisationName = $organisation ? $organisation->getName() : 'Unknown';

                $queues[$queueId]['tickets'][] = [
                    'id'           => $ticket->getId(),
                    'summary'      => $ticket->getShortDescription(),
                    'dueDate'      => $ticket->getDueDate(),
                    'overdueBy'    => $overdueBy,
                    'status'       => $statusName,
                    'assignedTo'   => $assignedTo,
                    'contactName'  => $contactName,
                    'organisation' => $organisationName,
                ];
            }

            $sentCount = 0;

            foreach ($queues as $group) {
                $queue = $group['queue'];
                if ($queue === null) {
                    $this->logger->warning('Overdue digest skipped for tickets with no queue');
                    continue;
                }

                $members = $queue->getMembers();
                if (count($members) === 0) {
                    $this->logger->warning('Overdue digest skipped for queue with no members', [
                        'queue' => $group['name'],
                    ]);
                    continue;
                }

                $subject = sprintf(
                    'Overdue tickets digest - %s (%d)',
                    $group['name'],
                    count($group['tickets'])
                );

                $body = $this->mailService->prepareBody('ticket_mail::ticket_overdue_digest', [
                    'queueName'   => $group['name'],
                    'tickets'     => $group['tickets'],
                    'generatedAt' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                ]);

                foreach ($members as $member) {
                    $this->mailService->send($member->getEmail(), $subject, $body);
                    $sentCount++;
                }
            }

            $output->writeln(sprintf('<info>Sent %d overdue digest emails</info>', $sentCount));
            $this->logger->info('Overdue digest sent', ['count' => $sentCount]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Fatal error: %s</error>', $e->getMessage()));
            $this->logger->critical('Overdue digest failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    private function formatDuration(Carbon $from, Carbon $to): string
    {
        $diff  = $from->diff($to);
        $parts = [];

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }

        if ($parts === []) {
            $parts[] = '0 minutes';
        }

        return implode(' ', $parts);
    }
}
