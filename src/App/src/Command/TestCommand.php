<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TestCommand class - provides an example of creating cli functionality
 *
 * @package App\Command
 */
class TestCommand extends Command
{
    /**
     * TestCommand constructor.
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure() : void
    {
        $this->setName('test:command')
            ->setDescription('A test console command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('This is a test command');
        return 0;
    }
}