<?php

namespace App\Command;

use App\Identity\Domain\Entity\Role;
use App\Identity\Domain\Repository\RoleRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-role',
    description: 'Creates a new role',
)]
class CreateRoleCommand extends Command
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the role')
            ->addArgument('description', InputArgument::OPTIONAL, 'The description of the role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $description = $input->getArgument('description');

        $role = $this->roleRepository->findByName($name);
        if ($role) {
            $output->writeln(sprintf('Role "%s" already exists.', $name));
            return Command::FAILURE;
        }

        $role = new Role($name, $description);
        $this->roleRepository->save($role, true);

        $output->writeln(sprintf('Role "%s" created successfully.', $name));

        return Command::SUCCESS;
    }
}
