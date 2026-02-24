<?php

declare(strict_types=1);

namespace App\Command;

use App\Encryption\Sodium;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSodiumKey extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('sodium:key')
            ->setDescription('Creates a sodium hex represented key');
    }

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(Sodium::generateKey());
        return 0;
    }
}
