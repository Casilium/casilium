<?php

declare(strict_types=1);

namespace User\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use User\Entity\Role;
use User\Entity\User;

use function bin2hex;
use function date;
use function password_hash;
use function random_bytes;
use function sprintf;

use const PASSWORD_BCRYPT;

class CreateUser extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function configure(): void
    {
        $this->setName('user:create')
            ->setDescription('Create or update a user')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Full name')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'User password (optional)')
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Role name', 'Administrator')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Status (0=inactive,1=active,2=retired)', '1')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Update existing user if email exists');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $email  = (string) $input->getOption('email');
        $name   = (string) $input->getOption('name');
        $role   = (string) $input->getOption('role');
        $status = (int) $input->getOption('status');
        $force  = (bool) $input->getOption('force');

        if ($email === '' || $name === '') {
            $output->writeln('Email and name are required.');
            return 1;
        }

        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user !== null && ! $force) {
            $output->writeln(sprintf('User already exists for email %s. Use --force to update.', $email));
            return 1;
        }

        $isNewUser = false;
        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
            $isNewUser = true;
        }

        $password = (string) $input->getOption('password');
        if ($password === '') {
            $password = bin2hex(random_bytes(12));
            $output->writeln(sprintf('Generated password: %s', $password));
        }

        $user->setFullName($name);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setStatus($status);
        if ($isNewUser) {
            $user->setDateCreated(date('Y-m-d H:i:s'));
        }

        if ($role !== '') {
            /** @var Role|null $roleEntity */
            $roleEntity = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $role]);
            if ($roleEntity === null) {
                $output->writeln(sprintf('Role not found: %s', $role));
                return 1;
            }
            $user->getRoles()->clear();
            $user->addRole($roleEntity);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('User %s created/updated.', $email));
        return 0;
    }
}
