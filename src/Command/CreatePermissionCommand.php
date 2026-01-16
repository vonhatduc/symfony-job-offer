<?php

namespace App\Command;

use App\Identity\Domain\Entity\Permission;
use App\Identity\Domain\Repository\PermissionRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-permission',
    description: 'Creates a new permission',
)]
class CreatePermissionCommand extends Command
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the permission')
            ->addArgument('description', InputArgument::OPTIONAL, 'The description of the permission')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $description = $input->getArgument('description');

        $permission = $this->permissionRepository->findByName($name);
        if ($permission) {
            $output->writeln(sprintf('Permission "%s" already exists.', $name));
            return Command::FAILURE;
        }

        $permission = new Permission($name, $description);
        $this->permissionRepository->save($permission, true);

        $output->writeln(sprintf('Permission "%s" created successfully.', $name));

        return Command::SUCCESS;
    }
}
