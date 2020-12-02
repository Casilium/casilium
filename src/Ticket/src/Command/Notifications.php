<?php

declare(strict_types=1);

namespace Ticket\Command;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;
use function gmdate;
use function sprintf;

class Notifications extends Command
{
    /** @var TicketService */
    protected $ticketService;

    public function __construct(TicketService $service)
    {
        parent::__construct();
        $this->ticketService = $service;
    }

    /**
     * Configure this command
     */
    public function configure(): void
    {
        $this->setName('ticket:notifications')
            ->setDescription('Send ticket notifications');
    }

    /**
     * @param InputInterface $input input interface
     * @param OutputInterface $output output interface
     * @return int exit code
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Ticket[] $tickets */
        $tickets = $this->ticketService->findTicketsDueWithin(30);
        if (! empty($tickets)) {
            foreach ($tickets as $ticket) {
                $now     = Carbon::now('UTC');
                $due     = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate());
                $seconds = $now->diffInSeconds($due);

                $output->writeln(sprintf(
                    'Ticket #%s is due in %s',
                    $ticket->getId(),
                    gmdate('H:i:s', $seconds)
                ));
            }
        }

        return 0;
    }
}
