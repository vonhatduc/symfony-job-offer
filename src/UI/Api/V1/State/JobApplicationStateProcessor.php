<?php

declare(strict_types=1);

namespace App\UI\Api\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\JobManagement\Application\Command\ApplyToJob\ApplyToJobCommand;
use App\UI\Api\V1\Resource\JobApplicationResource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Bridges JobApplication API request to the Messenger Command Bus
 */
final class JobApplicationStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly Security $security
    ) {
    }

    /**
     * @param JobApplicationResource $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JobApplicationResource
    {
        $user = $this->security->getUser();
        if (!$user instanceof \App\Identity\Domain\Entity\User) {
            throw new AccessDeniedHttpException('Authenticated user not found or invalid.');
        }

        $command = new ApplyToJobCommand(
            userId: $user->getId(),
            jobOfferId: (int) $data->jobOfferId
        );

        // Dispatch command and get the created application
        $envelope = $this->commandBus->dispatch($command);
        $application = $envelope->last(\Symfony\Component\Messenger\Stamp\HandledStamp::class)->getResult();

        // Transform to resource DTO
        $resource = new JobApplicationResource();
        $resource->id = $application->getId();
        $resource->jobOfferId = $application->getJobOffer()->getId();
        $resource->userEmail = $application->getUser()->getEmail();
        $resource->userName = $application->getUser()->getName();
        $resource->jobOfferTitle = $application->getJobOffer()->getTitle();
        $resource->createdAt = $application->getCreatedAt()->format('c');

        return $resource;
    }
}
