<?php

declare(strict_types=1);

namespace App\Tests\Unit\JobManagement\Application\Command;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\JobManagement\Application\Command\ApplyToJob\ApplyToJobCommand;
use App\JobManagement\Application\Command\ApplyToJob\ApplyToJobHandler;
use App\JobManagement\Domain\Entity\JobApplication;
use App\JobManagement\Domain\Entity\JobOffer;
use App\JobManagement\Domain\Exception\AlreadyAppliedException;
use App\JobManagement\Domain\Exception\JobClosedException;
use App\JobManagement\Domain\Repository\JobApplicationRepositoryInterface;
use App\JobManagement\Domain\Repository\JobOfferRepositoryInterface;
use App\Shared\Domain\Exception\DomainException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * TDD Tests for ApplyToJobHandler - Testing Advanced Patterns
 */
class ApplyToJobHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private JobOfferRepositoryInterface|MockObject $jobOfferRepository;
    private JobApplicationRepositoryInterface|MockObject $applicationRepository;
    private MessageBusInterface|MockObject $eventBus;
    private ApplyToJobHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->jobOfferRepository = $this->createMock(JobOfferRepositoryInterface::class);
        $this->applicationRepository = $this->createMock(JobApplicationRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new ApplyToJobHandler(
            $this->userRepository,
            $this->jobOfferRepository,
            $this->applicationRepository,
            $this->eventBus
        );
    }

    /**
     * @test
     */
    public function it_creates_application_and_dispatches_event(): void
    {
        // Arrange
        $user = new User();
        $jobOffer = new JobOffer('Developer', 'Great job');
        $jobOffer->setStatus('active');

        $command = new ApplyToJobCommand(userId: 1, jobOfferId: 1);

        $this->userRepository->method('findById')->with(1)->willReturn($user);
        $this->jobOfferRepository->method('findById')->with(1)->willReturn($jobOffer);
        $this->applicationRepository->method('findByUserAndJobOffer')->willReturn(null);
        
        // Assert event dispatch
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        // Act
        $application = ($this->handler)($command);

        // Assert
        $this->assertInstanceOf(JobApplication::class, $application);
        $this->assertSame($user, $application->getUser());
    }

    /**
     * @test
     */
    public function it_throws_domain_exception_when_user_not_found(): void
    {
        $command = new ApplyToJobCommand(userId: 999, jobOfferId: 1);
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User with ID 999 not found');

        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_throws_domain_exception_when_job_offer_not_found(): void
    {
        $user = new User();
        $command = new ApplyToJobCommand(userId: 1, jobOfferId: 999);

        $this->userRepository->method('findById')->willReturn($user);
        $this->jobOfferRepository->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Job offer with ID 999 not found');

        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_job_is_closed(): void
    {
        $user = new User();
        $jobOffer = new JobOffer('Developer', 'Desc');
        $jobOffer->setStatus('closed');
        
        $command = new ApplyToJobCommand(userId: 1, jobOfferId: 1);

        $this->userRepository->method('findById')->willReturn($user);
        $this->jobOfferRepository->method('findById')->willReturn($jobOffer);

        $this->expectException(JobClosedException::class);

        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_job_is_soft_deleted(): void
    {
        $user = new User();
        $jobOffer = new JobOffer('Developer', 'Desc');
        $jobOffer->delete(); // Soft delete
        
        $command = new ApplyToJobCommand(userId: 1, jobOfferId: 1);

        $this->userRepository->method('findById')->willReturn($user);
        $this->jobOfferRepository->method('findById')->willReturn($jobOffer);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('removed');

        ($this->handler)($command);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_already_applied(): void
    {
        $user = new User();
        $jobOffer = new JobOffer('Developer', 'Great job');
        $jobOffer->setStatus('active');
        $existingApplication = new JobApplication($user, $jobOffer);

        $command = new ApplyToJobCommand(userId: 1, jobOfferId: 1);

        $this->userRepository->method('findById')->willReturn($user);
        $this->jobOfferRepository->method('findById')->willReturn($jobOffer);
        $this->applicationRepository->method('findByUserAndJobOffer')->willReturn($existingApplication);

        $this->expectException(AlreadyAppliedException::class);

        ($this->handler)($command);
    }
}
