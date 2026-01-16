<?php

declare(strict_types=1);

namespace App\UI\Api\V1\Resource\Auth;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for Login Request - Used for Swagger Documentation
 */
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/v1/login',
            openapiContext: [
                'summary' => 'Get JWT Token',
                'description' => 'Authenticates a user and returns a JWT token.',
                'responses' => [
                    '200' => [
                        'description' => 'Login successful',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Invalid credentials',
                    ],
                ],
            ],
        )
    ],
    routePrefix: '',
    normalizationContext: ['groups' => ['auth:read']],
    denormalizationContext: ['groups' => ['auth:write']],
)]
final class Credentials
{
    #[ApiProperty(example: 'admin@example.com')]
    #[Assert\Email]
    #[Assert\NotBlank]
    public string $email;

    #[ApiProperty(example: 'password')]
    #[Assert\NotBlank]
    public string $password;
}
