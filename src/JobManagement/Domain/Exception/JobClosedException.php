<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class JobClosedException extends DomainException
{
    public function __construct(int $jobId)
    {
        parent::__construct(sprintf('Job offer with ID %d is closed and no longer accepting applications.', $jobId), 400);
    }
}
