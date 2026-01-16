<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Event;

use App\Shared\Domain\Event\DomainEventInterface;

/**
 * Dispatched when a user successfully applies to a job.
 * Allows adding notifications later without coupling logic.
 */
final class JobAppliedEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly int $jobOfferId,
        public readonly int $userId
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
