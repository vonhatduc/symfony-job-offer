<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Repository;

use App\Identity\Domain\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Identity\Domain\Repository\PermissionRepositoryInterface;

/**
 * @extends ServiceEntityRepository<Permission>
 */
class DoctrinePermissionRepository extends ServiceEntityRepository implements PermissionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function findByName(string $name): ?Permission
    {
        return $this->findOneBy(['name' => strtolower($name)]);
    }

    public function save(Permission $permission, bool $flush = false): void
    {
        $this->getEntityManager()->persist($permission);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
