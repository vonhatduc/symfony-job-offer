<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for Email Value Object
 */
class EmailTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_valid_email(): void
    {
        $email = Email::fromString('test@example.com');
        
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('test@example.com', $email->toString());
    }

    /**
     * @test
     */
    public function it_normalizes_email_to_lowercase(): void
    {
        $email = Email::fromString('TEST@EXAMPLE.COM');
        
        $this->assertEquals('test@example.com', $email->toString());
    }

    /**
     * @test
     */
    public function it_trims_whitespace(): void
    {
        $email = Email::fromString('  test@example.com  ');
        
        $this->assertEquals('test@example.com', $email->toString());
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email');
        
        Email::fromString('invalid-email');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_empty_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Email::fromString('');
    }

    /**
     * @test
     */
    public function it_can_compare_two_equal_emails(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('TEST@EXAMPLE.COM');
        
        $this->assertTrue($email1->equals($email2));
    }

    /**
     * @test
     */
    public function it_can_compare_two_different_emails(): void
    {
        $email1 = Email::fromString('test1@example.com');
        $email2 = Email::fromString('test2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_string(): void
    {
        $email = Email::fromString('test@example.com');
        
        $this->assertEquals('test@example.com', (string) $email);
    }
}
