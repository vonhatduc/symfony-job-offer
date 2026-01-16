<?php

declare(strict_types=1);

namespace App\UI\Api\V1\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\UI\Api\V1\State\JobOfferStateProvider;
use App\UI\Api\V1\State\JobOfferStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * JobOffer API Resource - Version 1
 */
#[ApiResource(
    shortName: 'JobOffer',
    routePrefix: '/v1',
    operations: [
        new GetCollection(
            uriTemplate: '/job-offers',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            provider: JobOfferStateProvider::class
        ),
        new Get(
            uriTemplate: '/job-offers/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            provider: JobOfferStateProvider::class
        )
    ],
    normalizationContext: ['groups' => ['job:read']],
    denormalizationContext: ['groups' => ['job:write']]
)]
#[ApiResource(
    shortName: 'JobOfferAdmin',
    routePrefix: '/admin/v1',
    operations: [
        new Post(
            uriTemplate: '/job-offers',
            security: "is_granted('ROLE_ADMIN')",
            processor: JobOfferStateProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['job:read']],
    denormalizationContext: ['groups' => ['job:write']]
)]
class JobOfferResource
{
    #[Groups(['job:read'])]
    public ?int $id = null;

    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    public ?string $description = null;

    #[Groups(['job:read', 'job:write'])]
    public ?string $companyName = null;

    #[Groups(['job:read', 'job:write'])]
    public ?string $location = null;

    #[Groups(['job:read', 'job:write'])]
    public ?string $salaryRange = null;

    #[Groups(['job:read', 'job:write'])]
    public ?string $employmentType = null;

    #[Groups(['job:read', 'job:write'])]
    public string $status = 'active';

    #[Groups(['job:read', 'job:write'])]
    public array $requirements = [];

    #[Groups(['job:read'])]
    public ?string $createdAt = null;

    #[Groups(['job:read'])]
    public ?string $updatedAt = null;

    #[Groups(['job:read'])]
    public int $applicationCount = 0;
}
