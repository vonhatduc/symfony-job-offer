<?php

namespace App\Command;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\Role;
use App\Identity\Domain\Entity\Permission;
use App\JobManagement\Domain\Entity\JobOffer;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Repository\RoleRepositoryInterface;
use App\Identity\Domain\Repository\PermissionRepositoryInterface;
use App\JobManagement\Infrastructure\Repository\DoctrineJobOfferRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:setup',
    description: 'Full project setup: creates all roles, permissions, default users, and jobs.',
)]
class SetupCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionRepositoryInterface $permissionRepository,
        private DoctrineJobOfferRepository $jobOfferRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting Project Setup');

        // 1. Create Permissions
        $permissionsData = [
            'user:read' => 'Permission to list all users',
            'user:write' => 'Permission to create/edit users',
            'job:read' => 'Permission to view job offers',
            'job:write' => 'Permission to manage job offers',
            'application:write' => 'Permission to apply for jobs',
            'application:read' => 'Permission to view applications',
        ];

        $permissions = [];
        foreach ($permissionsData as $name => $desc) {
            $permission = $this->permissionRepository->findByName($name);
            if (!$permission) {
                $permission = new Permission($name, $desc);
                $this->permissionRepository->save($permission, true);
                $io->note(sprintf('Created permission: %s', $name));
            }
            $permissions[$name] = $permission;
        }

        // 2. Create Roles
        // ADMIN Role
        $adminRole = $this->roleRepository->findByName('ADMIN');
        if (!$adminRole) {
            $adminRole = new Role('ADMIN', 'Super Administrator');
            $this->roleRepository->save($adminRole, true);
            $io->note('Created role: ADMIN');
        }
        // Link all permissions to ADMIN
        foreach ($permissions as $p) {
            $adminRole->addPermission($p);
        }
        $this->roleRepository->save($adminRole, true);

        // MEMBER Role
        $memberRole = $this->roleRepository->findByName('MEMBER');
        if (!$memberRole) {
            $memberRole = new Role('MEMBER', 'Regular Application User');
            $this->roleRepository->save($memberRole, true);
            $io->note('Created role: MEMBER');
        }
        // Link specific permissions to MEMBER
        if (isset($permissions['job:read'])) $memberRole->addPermission($permissions['job:read']);
        if (isset($permissions['application:write'])) $memberRole->addPermission($permissions['application:write']);
        $this->roleRepository->save($memberRole, true);

        // 3. Create Default Admin User
        $adminEmail = 'admin@example.com';
        $adminUser = $this->userRepository->findAll(); // Simple check for any user or specific email
        $existingAdmin = null;
        foreach ($adminUser as $u) {
            if ($u->getEmail() === $adminEmail) {
                $existingAdmin = $u;
                break;
            }
        }

        if (!$existingAdmin) {
            $admin = new User();
            $admin->setEmail($adminEmail);
            $admin->setName('System Admin');
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
            $admin->addRole($adminRole);
            $this->userRepository->save($admin, true);
            $io->success('Created default admin user: admin@example.com / password');
        }

        // 4. Create Default Regular User
        $userEmail = 'user@example.com';
        $existingUser = null;
        foreach ($adminUser as $u) {
            if ($u->getEmail() === $userEmail) {
                $existingUser = $u;
                break;
            }
        }

        if (!$existingUser) {
            $user = new User();
            $user->setEmail($userEmail);
            $user->setName('Regular User');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->addRole($memberRole);
            $this->userRepository->save($user, true);
            $io->success('Created default regular user: user@example.com / password');
        }

        // 5. Create Default Jobs
        if (count($this->jobOfferRepository->findAll()) === 0) {
            $jobs = [
                [
                    'title' => 'Senior PHP Backend Engineer',
                    'company' => 'Google',
                    'location' => 'Mountain View, CA (Remote)',
                    'salary' => '$150k - $220k',
                    'type' => 'Full-time',
                    'desc' => 'Driving the future of scalable architecture using PHP 8.3, Symfony, and DDD principles.',
                    'reqs' => ['PHP 8.3', 'Symfony 7', 'Domain Driven Design (DDD)', 'AWS Cloud Architecture']
                ],
                [
                    'title' => 'Frontend Architect',
                    'company' => 'Meta',
                    'location' => 'Menlo Park, CA',
                    'salary' => '$140k - $200k',
                    'type' => 'Contract',
                    'desc' => 'Leading the frontend evolution of social connectivity through advanced React and TypeScript patterns.',
                    'reqs' => ['React 18', 'TypeScript', 'Next.js', 'Tailwind CSS']
                ],
                [
                    'title' => 'Staff Backend Developer',
                    'company' => 'Netflix',
                    'location' => 'Los Gatos, CA',
                    'salary' => '$160k - $250k',
                    'type' => 'Full-time',
                    'desc' => 'Optimizing high-throughput video streaming services and microservices infrastructure.',
                    'reqs' => ['Java/Spring Boot', 'Microservices', 'Cassandra', 'System Design']
                ],
            ];

            foreach ($jobs as $jobData) {
                $job = new JobOffer($jobData['title'], $jobData['desc']);
                $job->setCompanyName($jobData['company']);
                $job->setLocation($jobData['location']);
                $job->setSalaryRange($jobData['salary']);
                $job->setEmploymentType($jobData['type']);
                $job->setRequirements($jobData['reqs']);
                $job->setStatus('active');
                
                $this->jobOfferRepository->save($job, true);
            }
            $io->note('Created professional-grade job offers.');
        }

        $io->success('Setup completed successfully!');

        return Command::SUCCESS;
    }
}
