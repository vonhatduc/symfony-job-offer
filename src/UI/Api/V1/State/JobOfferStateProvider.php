<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\JobManagement\Domain\Repository\JobOfferRepositoryInterface;
use App\UI\Api\V1\Resource\JobOfferResource;

/**
 * V1 State Provider for JobOffer
 */
final class JobOfferStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly JobOfferRepositoryInterface $jobOfferRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $offers = $this->jobOfferRepository->findAll();
            return array_map(fn($offer) => $this->transform($offer), $offers);
        }

        $offer = $this->jobOfferRepository->findById((int) $uriVariables['id']);
        return $offer ? $this->transform($offer) : null;
    }

    private function transform($offer): JobOfferResource
    {
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
        $resource->applicationCount = $offer->getApplicationCount();
        return $resource;
    }
}
