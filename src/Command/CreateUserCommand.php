<?php

namespace App\Command;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\Role;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Repository\RoleRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new regular user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private RoleRepositoryInterface $roleRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        
        // Find or create MEMBER role
        $role = $this->roleRepository->findByName('MEMBER');
        if (!$role) {
            $role = new Role('MEMBER', 'Regular Member');
            $this->roleRepository->save($role);
        }
        $user->addRole($role);

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        $this->userRepository->save($user, true);

        $output->writeln('User created successfully.');

        return Command::SUCCESS;
    }
}
