<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Entity\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;
    
    public function findByName(string $name): ?Role;
    
    public function findByIds(array $ids): array;
    
    public function save(Role $role): void;
}
