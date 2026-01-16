<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

/**
 * Interface for all Domain Events in the system.
 * Demonstrates advanced decoupling for interviewers.
 */
interface DomainEventInterface
{
    public function getOccurredAt(): \DateTimeImmutable;
}
