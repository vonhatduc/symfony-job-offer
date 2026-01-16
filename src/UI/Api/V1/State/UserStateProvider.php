<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\UI\Api\V1\Resource\UserResource;

/**
 * V1 State Provider for User
 */
final class UserStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $users = $this->userRepository->findAll();
            return array_map(fn($user) => $this->transform($user), $users);
        }

        $user = $this->userRepository->findById((int) $uriVariables['id']);
        return $user ? $this->transform($user) : null;
    }

    private function transform($user): UserResource
    {
        $resource = new UserResource();
        $resource->id = $user->getId();
        $resource->email = $user->getEmail();
        $resource->name = $user->getName();
        $resource->roles = $user->getRoles();
        $resource->createdAt = $user->getCreatedAt()->format('c');
        $resource->updatedAt = $user->getUpdatedAt()->format('c');
        return $resource;
    }
}
