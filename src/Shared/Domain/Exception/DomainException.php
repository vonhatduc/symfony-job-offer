<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * Base Domain Exception
 * Can be instantiated directly for generic business errors or extended.
 */
class DomainException extends \Exception
{
    public function __construct(string $message = "", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
