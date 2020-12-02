<?php

declare(strict_types=1);

namespace Ticket\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Service\TicketService;

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
        $output->writeln('Hello World');

        return 0;
    }
}
