<?php

declare(strict_types=1);

namespace App\UI\Api\V1\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\UI\Api\V1\State\JobApplicationStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * JobApplication API Resource - Version 1
 * Allows members to apply for job offers.
 */
#[ApiResource(
    shortName: 'JobApplication',
    routePrefix: '/v1',
    operations: [
        new Post(
            uriTemplate: '/job-applications',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            processor: JobApplicationStateProcessor::class,
            openapiContext: [
                'summary' => 'Apply for a job offer',
                'description' => 'Creates a new application for the specified job offer. The user is automatically resolved from the token.',
                'responses' => [
                    '201' => ['description' => 'Application successful'],
                    '400' => ['description' => 'Invalid input or business rule violation'],
                    '401' => ['description' => 'Authentication required'],
                ],
            ]
        )
    ],
    normalizationContext: ['groups' => ['app:read']],
    denormalizationContext: ['groups' => ['app:write']]
)]
#[ApiResource(
    shortName: 'JobApplicationAdmin',
    routePrefix: '/admin/v1',
    operations: [
        new GetCollection(
            uriTemplate: '/job-applications',
            security: "is_granted('ROLE_ADMIN')",
            provider: \App\UI\Api\V1\State\JobApplicationStateProvider::class,
            openapiContext: [
                'summary' => 'List all job applications (Admin only)',
                'description' => 'Allows administrators to view all job applications. Use query parameter `jobOfferId` to filter by job.',
                'parameters' => [
                    [
                        'name' => 'jobOfferId',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter applications by job offer ID',
                        'schema' => ['type' => 'integer']
                    ]
                ]
            ]
        )
    ],
    normalizationContext: ['groups' => ['app:read']]
)]
final class JobApplicationResource
{
    #[Groups(['app:read'])]
    public ?int $id = null;

    #[Groups(['app:write'])]
    #[Assert\NotBlank]
    public ?int $jobOfferId = null;

    #[Groups(['app:read'])]
    public ?string $userEmail = null;

    #[Groups(['app:read'])]
    public ?string $userName = null;

    #[Groups(['app:read'])]
    public ?string $jobOfferTitle = null;

    #[Groups(['app:read'])]
    public ?string $createdAt = null;
}
