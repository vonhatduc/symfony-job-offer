<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Repository;

use App\Identity\Domain\Entity\User;
use App\JobManagement\Domain\Entity\JobApplication;
use App\JobManagement\Domain\Entity\JobOffer;

interface JobApplicationRepositoryInterface
{
    public function findById(int $id): ?JobApplication;
    
    public function findByUserAndJobOffer(User $user, JobOffer $jobOffer): ?JobApplication;
    
    public function findByJobOffer(JobOffer $jobOffer): array;
    
    public function countByJobOffer(JobOffer $jobOffer): int;
    
    public function save(JobApplication $application, bool $flush = false): void;
}
