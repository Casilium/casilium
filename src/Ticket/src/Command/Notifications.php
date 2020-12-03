<?php

declare(strict_types=1);

namespace Ticket\Command;

use Carbon\Carbon;
use MailService\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    public function __construct(TicketService $service, MailService $mailService)
    {
        parent::__construct();

        $this->ticketService = $service;
        $this->mailService   = $mailService;
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
        $target     = null;
        $targetType = null;

        $target         = (int) $input->getArgument('target');
        $periodArgument = strtolower($input->getArgument('period'));

        switch ($periodArgument) {
            case 'minutes':
                $targetType = TicketService::DUE_PERIOD_MINUTES;
                break;
            case 'hours':
                $targetType = TicketService::DUE_PERIOD_HOURS;
                break;
            case 'days':
                $targetType = TicketService::DUE_PERIOD_DAYS;
                break;
            case 'weeks':
                $targetType = TicketService::DUE_PERIOD_WEEKS;
                break;
            case 'months':
                $targetType = TicketService::DUE_PERIOD_MONTHS;
                break;
        }
        if ($targetType === null) {
            $output->writeln('Invalid argument, use minutes|hours|days|weeks|months');
            return 1;
        }

        /** @var Ticket[] $tickets */
        $tickets = $this->ticketService->findTicketsDueWithin($target, $targetType);

        if (! empty($tickets)) {
            foreach ($tickets as $ticket) {
                $now     = Carbon::now('UTC');
                $due     = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate());
                $seconds = $now->diffInSeconds($due);

                $output->writeln(
                    sprintf(
                        'Ticket #%s is due in %s',
                        $ticket->getId(),
                        gmdate('H:i:s', $seconds)
                    )
                );

                $this->ticketService->sendNotificationEmail($ticket, $target, $targetType);
            }
        }

        $tickets = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
            ->findOverdueTickets();

        foreach ($tickets as $ticket) {
            $output->writeln(sprintf('Ticket #%s is now overdue', $ticket->getId()));
            $this->ticketService->sendOverdueNotificationEmail($ticket);
        }
        return 0;
    }
}
