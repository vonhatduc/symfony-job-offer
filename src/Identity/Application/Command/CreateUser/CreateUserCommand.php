<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreateUser;

/**
 * CreateUser Command - Immutable DTO
 * Command for creating a new user
 */
final class CreateUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $name,
        public readonly array $roleIds = []
    ) {
    }
}
