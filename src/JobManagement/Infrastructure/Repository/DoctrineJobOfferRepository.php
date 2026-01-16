<?php

declare(strict_types=1);

namespace App\JobManagement\Infrastructure\Repository;

use App\JobManagement\Domain\Entity\JobOffer;
use App\JobManagement\Domain\Repository\JobOfferRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 */
class DoctrineJobOfferRepository extends ServiceEntityRepository implements JobOfferRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

    public function findById(int $id): ?JobOffer
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(JobOffer $jobOffer, bool $flush = false): void
    {
        $this->getEntityManager()->persist($jobOffer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JobOffer $jobOffer, bool $flush = false): void
    {
        $this->getEntityManager()->remove($jobOffer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
