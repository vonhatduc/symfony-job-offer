<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\JobManagement\Domain\Entity\JobOffer;
use App\JobManagement\Domain\Repository\JobOfferRepositoryInterface;
use App\UI\Api\V1\Resource\JobOfferResource;

/**
 * V1 State Processor for JobOffer
 */
final class JobOfferStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly JobOfferRepositoryInterface $jobOfferRepository
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JobOfferResource
    {
        if (!$data instanceof JobOfferResource) {
            throw new \InvalidArgumentException('Expected JobOfferResource');
        }

        $offer = new JobOffer($data->title, $data->description);
        $offer->setCompanyName($data->companyName);
        $offer->setLocation($data->location);
        $offer->setSalaryRange($data->salaryRange);
        $offer->setEmploymentType($data->employmentType);
        $offer->setStatus($data->status);
        $offer->setRequirements($data->requirements);
        
        $this->jobOfferRepository->save($offer, true);

        $resource = new JobOfferResource();
        $resource->id = $offer->getId();
        $resource->title = $offer->getTitle();
        $resource->description = $offer->getDescription();
        $resource->companyName = $offer->getCompanyName();
        $resource->location = $offer->getLocation();
        $resource->salaryRange = $offer->getSalaryRange();
        $resource->employmentType = $offer->getEmploymentType();
        $resource->status = $offer->getStatus();
        $resource->requirements = $offer->getRequirements() ?? [];
        $resource->createdAt = $offer->getCreatedAt()->format('c');
        $resource->updatedAt = $offer->getUpdatedAt()->format('c');
        $resource->applicationCount = 0;

        return $resource;
    }
}
