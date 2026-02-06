<?php

declare(strict_types=1);

namespace User\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Entity\User;

class CountUser extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function configure(): void
    {
        $this->setName('user:count')
            ->setDescription('Count users');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = (int) $this->entityManager->getRepository(User::class)->count([]);
        $output->writeln((string) $count);
        return 0;
    }
}
