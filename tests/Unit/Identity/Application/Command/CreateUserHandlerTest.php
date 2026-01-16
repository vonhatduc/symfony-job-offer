<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Application\Command;

use App\Identity\Application\Command\CreateUser\CreateUserCommand;
use App\Identity\Application\Command\CreateUser\CreateUserHandler;
use App\Identity\Domain\Entity\Role;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Exception\UserAlreadyExistsException;
use App\Identity\Domain\Repository\RoleRepositoryInterface;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\PasswordHasherInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * TDD Tests for CreateUserHandler
 * Tests business logic in isolation using mocks
 */
class CreateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordHasherInterface|MockObject $passwordHasher;
    private RoleRepositoryInterface|MockObject $roleRepository;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->roleRepository = $this->createMock(RoleRepositoryInterface::class);
        
        $this->handler = new CreateUserHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->roleRepository
        );
    }

    /**
     * @test
     */
    public function it_creates_user_with_valid_data(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'test@example.com',
            password: 'password123',
            name: 'Test User'
        );

        $this->userRepository
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        $this->passwordHasher
            ->method('hash')
            ->willReturn('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('save');

        // Act
        $user = ($this->handler)($command);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_user_already_exists(): void
    {
        // Arrange
        $existingUser = new User();
        $existingUser->setEmail('existing@example.com');

        $command = new CreateUserCommand(
            email: 'existing@example.com',
            password: 'password123',
            name: 'Test User'
        );

        $this->userRepository
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        // Assert
        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('User with email "existing@example.com" already exists.');

        // Act
        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_validates_email_format(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'invalid-email',
            password: 'password123',
            name: 'Test User'
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_hashes_password(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'test@example.com',
            password: 'plainPassword',
            name: 'Test User'
        );

        $this->userRepository
            ->method('findByEmail')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with($this->isInstanceOf(User::class), 'plainPassword')
            ->willReturn('$2y$10$hashedPassword');

        // Act
        $user = ($this->handler)($command);

        // Assert
        $this->assertEquals('$2y$10$hashedPassword', $user->getPassword());
    }

    /**
     * @test
     */
    public function it_assigns_roles_when_provided(): void
    {
        // Arrange
        $adminRole = new Role('ADMIN');
        
        $command = new CreateUserCommand(
            email: 'test@example.com',
            password: 'password123',
            name: 'Test User',
            roleIds: [1]
        );

        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hash')->willReturn('hashed');
        
        $this->roleRepository
            ->expects($this->once())
            ->method('findByIds')
            ->with([1])
            ->willReturn([$adminRole]);

        // Act
        $user = ($this->handler)($command);

        // Assert
        $this->assertTrue($user->hasRole('ADMIN'));
    }

    /**
     * @test
     */
    public function it_normalizes_email_to_lowercase(): void
    {
        // Arrange
        $command = new CreateUserCommand(
            email: 'TEST@EXAMPLE.COM',
            password: 'password123',
            name: 'Test User'
        );

        $this->userRepository
            ->method('findByEmail')
            ->with('test@example.com') // Expects lowercase
            ->willReturn(null);

        $this->passwordHasher->method('hash')->willReturn('hashed');

        // Act
        $user = ($this->handler)($command);

        // Assert
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}
