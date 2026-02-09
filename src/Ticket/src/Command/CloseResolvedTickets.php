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

class CloseResolvedTickets extends Command
{
    protected TicketService $ticketService;
    private int $autoCloseDays;

    public function __construct(TicketService $service, int $autoCloseDays = 2)
    {
        parent::__construct();
        $this->ticketService = $service;
        $this->autoCloseDays = $autoCloseDays;
    }

    /**
     * Configure this command
     */
    public function configure(): void
    {
        $this->setName('ticket:close-resolved')
            ->setDescription('Close resolved tickets');
    }

    /**
     * @param InputInterface $input input interface
     * @param OutputInterface $output output interface
     * @return int exit code
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $count = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                ->closeResolvedTickets($this->autoCloseDays);

            $output->writeln(sprintf('Closed %s tickets', $count));
        } catch (Exception $exception) {
            $output->writeln(sprintf('An error occurred: %s', $exception->getMessage()));
            return 1;
        }

        return 0;
    }
}
