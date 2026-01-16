<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Entity\User;

/**
 * User Repository Interface - Domain Layer
 */
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findAll(): array;
    
    public function save(User $user, bool $flush = false): void;
    
    public function remove(User $user, bool $flush = false): void;
}
