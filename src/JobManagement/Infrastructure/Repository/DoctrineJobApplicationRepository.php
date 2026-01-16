<?php

declare(strict_types=1);

namespace App\JobManagement\Infrastructure\Repository;

use App\Identity\Domain\Entity\User;
use App\JobManagement\Domain\Entity\JobApplication;
use App\JobManagement\Domain\Entity\JobOffer;
use App\JobManagement\Domain\Repository\JobApplicationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobApplication>
 */
class DoctrineJobApplicationRepository extends ServiceEntityRepository implements JobApplicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobApplication::class);
    }

    public function findById(int $id): ?JobApplication
    {
        return $this->find($id);
    }

    public function findByUserAndJobOffer(User $user, JobOffer $jobOffer): ?JobApplication
    {
        return $this->findOneBy([
            'user' => $user,
            'jobOffer' => $jobOffer
        ]);
    }

    public function findByJobOffer(JobOffer $jobOffer): array
    {
        return $this->findBy(['jobOffer' => $jobOffer]);
    }

    public function countByJobOffer(JobOffer $jobOffer): int
    {
        return $this->count(['jobOffer' => $jobOffer]);
    }

    public function save(JobApplication $application, bool $flush = false): void
    {
        $this->getEntityManager()->persist($application);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
