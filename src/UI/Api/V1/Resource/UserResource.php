<?php

declare(strict_types=1);

namespace App\UI\Api\V1\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\UI\Api\V1\State\UserStateProvider;
use App\UI\Api\V1\State\UserStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User API Resource - Version 1
 */
#[ApiResource(
    shortName: 'User',
    routePrefix: '/admin/v1',
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            security: "is_granted('ROLE_ADMIN')",
            provider: UserStateProvider::class
        ),
        new Get(
            uriTemplate: '/users/{id}',
            security: "is_granted('ROLE_ADMIN') or object.id == user.getId()",
            provider: UserStateProvider::class
        ),
        new Post(
            uriTemplate: '/users',
            security: "is_granted('ROLE_ADMIN')",
            processor: UserStateProcessor::class,
            validationContext: ['groups' => ['user:create']]
        ),
        new Patch(
            uriTemplate: '/users/{id}',
            security: "is_granted('ROLE_ADMIN') or object.id == user.getId()",
            processor: UserStateProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class UserResource
{
    #[Groups(['user:read'])]
    public ?int $id = null;

    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Groups(['user:write'])]
    #[Assert\NotBlank(groups: ['user:create'])]
    public ?string $password = null;

    #[Groups(['user:read'])]
    public array $roles = [];

    /** @var int[] */
    #[Groups(['user:write'])]
    public array $roleIds = [];

    #[Groups(['user:read'])]
    public ?string $createdAt = null;

    #[Groups(['user:read'])]
    public ?string $updatedAt = null;
}
