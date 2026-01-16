<?php

declare(strict_types=1);

namespace App\JobManagement\Application\Command\ApplyToJob;

/**
 * ApplyToJob Command - Immutable DTO
 */
final class ApplyToJobCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $jobOfferId
    ) {
    }
}
