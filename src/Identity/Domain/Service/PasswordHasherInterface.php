<?php

declare(strict_types=1);

namespace App\Identity\Domain\Service;

use App\Identity\Domain\Entity\User;

/**
 * Password Hasher Interface - Domain Service
 * Infrastructure will provide the implementation
 */
interface PasswordHasherInterface
{
    public function hash(User $user, string $plainPassword): string;
    
    public function verify(User $user, string $plainPassword): bool;
}
