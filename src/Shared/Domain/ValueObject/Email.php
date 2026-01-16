<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Email Value Object - Immutable
 */
final class Email
{
    private string $value;

    private function __construct(string $email)
    {
        $this->value = $email;
    }

    public static function fromString(string $email): self
    {
        $normalized = strtolower(trim($email));
        
        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email: %s', $email));
        }
        
        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
