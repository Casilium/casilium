<?php

declare(strict_types=1);

namespace App\Command;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TestCommand class - provides an example of creating cli functionality
 */
class TestCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName('test:command')
            ->setDescription('A test console command');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This is a test command');
        return 0;
    }
}
