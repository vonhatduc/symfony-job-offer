<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\JobManagement\Domain\Repository\JobApplicationRepositoryInterface;
use App\UI\Api\V1\Resource\JobApplicationResource;

/**
 * V1 State Provider for JobApplication (Admin monitoring)
 */
final class JobApplicationStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly JobApplicationRepositoryInterface $applicationRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $filters = $context['filters'] ?? [];
            $jobOfferId = $filters['jobOfferId'] ?? null;

            $applications = $this->applicationRepository->findAll();

            // Filter by job offer if specified
            if ($jobOfferId) {
                $applications = array_filter(
                    $applications,
                    fn($app) => $app->getJobOffer()->getId() === (int) $jobOfferId
                );
            }

            return array_map(fn($app) => $this->transform($app), array_values($applications));
        }

        return null;
    }

    private function transform($application): JobApplicationResource
    {
        $resource = new JobApplicationResource();
        $resource->id = $application->getId();
        $resource->userEmail = $application->getUser()->getEmail();
        $resource->userName = $application->getUser()->getName();
        $resource->jobOfferId = $application->getJobOffer()->getId();
        $resource->jobOfferTitle = $application->getJobOffer()->getTitle();
        $resource->createdAt = $application->getCreatedAt()->format('c');
        
        return $resource;
    }
}
