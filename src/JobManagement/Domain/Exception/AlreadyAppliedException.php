<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class AlreadyAppliedException extends DomainException
{
    public function __construct(int $userId, int $jobOfferId)
    {
        parent::__construct(
            sprintf('User %d has already applied to job offer %d.', $userId, $jobOfferId)
        );
    }
}
