<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreateUser;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Exception\UserAlreadyExistsException;
use App\Identity\Domain\Repository\RoleRepositoryInterface;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\ValueObject\Email;

/**
 * Handles user creation requests
 */
final class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly RoleRepositoryInterface $roleRepository
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function __invoke(CreateUserCommand $command): User
    {
        // Validate email format using Value Object
        $email = Email::fromString($command->email);
        
        // Check if user already exists
        if ($this->userRepository->findByEmail($email->toString())) {
            throw new UserAlreadyExistsException($email->toString());
        }
        
        // Create new user
        $user = new User();
        $user->setEmail($email->toString());
        $user->setName($command->name);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hash($user, $command->password);
        $user->setPassword($hashedPassword);
        
        // Assign roles if provided
        if (!empty($command->roleIds)) {
            $roles = $this->roleRepository->findByIds($command->roleIds);
            foreach ($roles as $role) {
                $user->addRole($role);
            }
        }
        
        // Persist
        $this->userRepository->save($user, true);
        
        return $user;
    }
}
