<?php

declare(strict_types=1);

namespace Ticket\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;
use function sprintf;

class UpdateWaitingTickets extends Command
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
        $this->setName('ticket:update-waiting')
            ->setDescription('Update due time for waiting tickets');
    }

    /**
     * @param InputInterface $input input interface
     * @param OutputInterface $output output interface
     * @return int exit code
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $updatedTickets = $this->ticketService->updateWaitingTickets();
            if (! empty($updatedTickets)) {
                foreach ($updatedTickets as $ticketId => $ticketData) {
                    $output->writeln(sprintf(
                        'Ticket #%s due time was updated from %s to %s',
                        $ticketId,
                        $ticketData['was_due'],
                        $ticketData['now_due']
                    ));
                }
            }
        } catch (Exception $exception) {
            $output->writeln(sprintf('An error occurred: %s', $exception->getMessage()));
            return 1;
        }

        return 0;
    }
}
