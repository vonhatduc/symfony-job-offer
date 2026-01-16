<?php

namespace App\Command;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a new admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private \App\Identity\Domain\Repository\RoleRepositoryInterface $roleRepository,
        private \App\Identity\Domain\Repository\PermissionRepositoryInterface $permissionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the admin')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the admin')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the admin')
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
        
        // Find or create ADMIN role
        $role = $this->roleRepository->findByName('ADMIN');
        if (!$role) {
            $role = new \App\Identity\Domain\Entity\Role('ADMIN', 'Administrator');
            $this->roleRepository->save($role);
        }

        // Add default permissions to Admin role
        $permissions = [
            'user:read' => 'Permission to read users',
            'user:write' => 'Permission to create/edit users',
            'job:write' => 'Permission to create/edit job offers',
        ];

        foreach ($permissions as $name => $desc) {
            $p = $this->permissionRepository->findByName($name);
            if (!$p) {
                $p = new \App\Identity\Domain\Entity\Permission($name, $desc);
                $this->permissionRepository->save($p);
            }
            $role->addPermission($p);
        }
        
        $this->roleRepository->save($role, true);

        $user->addRole($role);

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        $this->userRepository->save($user, true);

        $output->writeln('Admin user created successfully with default permissions.');

        return Command::SUCCESS;
    }
}
