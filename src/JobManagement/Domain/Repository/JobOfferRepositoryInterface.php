<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Repository;

use App\JobManagement\Domain\Entity\JobOffer;

interface JobOfferRepositoryInterface
{
    public function findById(int $id): ?JobOffer;
    
    public function findAll(): array;
    
    public function save(JobOffer $jobOffer, bool $flush = false): void;
    
    public function remove(JobOffer $jobOffer, bool $flush = false): void;
}
