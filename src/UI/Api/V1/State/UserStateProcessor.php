<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Identity\Application\Command\CreateUser\CreateUserCommand;
use App\Identity\Application\Command\CreateUser\CreateUserHandler;
use App\UI\Api\V1\Resource\UserResource;

/**
 * V1 State Processor for User
 */
final class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CreateUserHandler $createUserHandler
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        if (!$data instanceof UserResource) {
            throw new \InvalidArgumentException('Expected UserResource');
        }

        $command = new CreateUserCommand(
            email: $data->email,
            password: $data->password ?? '',
            name: $data->name,
            roleIds: $data->roleIds
        );

        $user = ($this->createUserHandler)($command);

        $resource = new UserResource();
        $resource->id = $user->getId();
        $resource->email = $user->getEmail();
        $resource->roles = $user->getRoles();
        $resource->createdAt = $user->getCreatedAt()->format('c');
        $resource->updatedAt = $user->getUpdatedAt()->format('c');

        return $resource;
    }
}
