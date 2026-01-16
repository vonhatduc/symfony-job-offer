<?php

declare(strict_types=1);

namespace App\JobManagement\Application\Command\ApplyToJob;

use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\JobManagement\Domain\Entity\JobApplication;
use App\JobManagement\Domain\Exception\AlreadyAppliedException;
use App\JobManagement\Domain\Event\JobAppliedEvent;
use App\JobManagement\Domain\Exception\JobClosedException;
use App\JobManagement\Domain\Repository\JobApplicationRepositoryInterface;
use App\JobManagement\Domain\Repository\JobOfferRepositoryInterface;
use App\Shared\Domain\Exception\DomainException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for job application requests
 */
#[AsMessageHandler]
final class ApplyToJobHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JobOfferRepositoryInterface $jobOfferRepository,
        private readonly JobApplicationRepositoryInterface $applicationRepository,
        private readonly MessageBusInterface $eventBus
    ) {
    }

    /**
     * @throws AlreadyAppliedException
     * @throws JobClosedException
     * @throws DomainException
     */
    public function __invoke(ApplyToJobCommand $command): JobApplication
    {
        // 1. Find user (Infrastructure check)
        $user = $this->userRepository->findById($command->userId);
        if (!$user) {
            throw new DomainException(sprintf('User with ID %d not found.', $command->userId), 404);
        }

        // 2. Find job offer
        $jobOffer = $this->jobOfferRepository->findById($command->jobOfferId);
        if (!$jobOffer || $jobOffer->isDeleted()) {
            throw new DomainException(sprintf('Job offer with ID %d not found or has been removed.', $command->jobOfferId), 404);
        }

        // 3. Business Guard: Only apply to active jobs
        if ($jobOffer->getStatus() !== 'active') {
            throw new JobClosedException($command->jobOfferId);
        }

        // 4. Persistence Guard: Avoid duplicates
        $existingApplication = $this->applicationRepository->findByUserAndJobOffer($user, $jobOffer);
        if ($existingApplication) {
            throw new AlreadyAppliedException($command->userId, $command->jobOfferId);
        }

        // 5. Execution
        $application = new JobApplication($user, $jobOffer);
        $this->applicationRepository->save($application, true);

        // 6. Decoupled side-effect (Domain Event)
        // Dispatch domain event for side effects (e.g., notifications)
        $this->eventBus->dispatch(new JobAppliedEvent($command->jobOfferId, $command->userId));

        return $application;
    }
}
