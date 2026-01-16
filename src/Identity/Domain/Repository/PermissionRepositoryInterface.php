<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Entity\Permission;

interface PermissionRepositoryInterface
{
    public function findByName(string $name): ?Permission;
    public function save(Permission $permission, bool $flush = false): void;
}
