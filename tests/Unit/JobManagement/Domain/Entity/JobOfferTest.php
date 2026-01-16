<?php

declare(strict_types=1);

namespace App\Tests\Unit\JobManagement\Domain\Entity;

use App\JobManagement\Domain\Entity\JobOffer;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for JobOffer Entity
 */
class JobOfferTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_job_offer_with_title_and_description(): void
    {
        $jobOffer = new JobOffer('Symfony Developer', 'Great opportunity');

        $this->assertEquals('Symfony Developer', $jobOffer->getTitle());
        $this->assertEquals('Great opportunity', $jobOffer->getDescription());
    }

    /**
     * @test
     */
    public function it_sets_created_at_automatically(): void
    {
        $before = new \DateTimeImmutable();
        $jobOffer = new JobOffer('Developer', 'Description');
        $jobOffer->setCreatedAtValue(); // Simulate Doctrine PrePersist
        $after = new \DateTimeImmutable();

        $this->assertInstanceOf(\DateTimeImmutable::class, $jobOffer->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $jobOffer->getCreatedAt());
        $this->assertLessThanOrEqual($after, $jobOffer->getCreatedAt());
    }

    /**
     * @test
     */
    public function it_can_set_professional_fields(): void
    {
        $jobOffer = new JobOffer('Developer', 'Description');
        
        $jobOffer->setCompanyName('Google');
        $jobOffer->setLocation('Mountain View');
        $jobOffer->setSalaryRange('$100k - $150k');
        $jobOffer->setEmploymentType('Full-time');
        $jobOffer->setStatus('draft');
        $jobOffer->setRequirements(['PHP', 'Symfony']);

        $this->assertEquals('Google', $jobOffer->getCompanyName());
        $this->assertEquals('Mountain View', $jobOffer->getLocation());
        $this->assertEquals('$100k - $150k', $jobOffer->getSalaryRange());
        $this->assertEquals('Full-time', $jobOffer->getEmploymentType());
        $this->assertEquals('draft', $jobOffer->getStatus());
        $this->assertEquals(['PHP', 'Symfony'], $jobOffer->getRequirements());
    }

    /**
     * @test
     */
    public function it_starts_with_active_status_and_empty_requirements(): void
    {
        $jobOffer = new JobOffer('Developer', 'Description');

        $this->assertEquals('active', $jobOffer->getStatus());
        $this->assertEquals([], $jobOffer->getRequirements());
    }

    /**
     * @test
     */
    public function it_can_be_soft_deleted(): void
    {
        $jobOffer = new JobOffer('Developer', 'Description');
        
        $this->assertFalse($jobOffer->isDeleted());
        
        $jobOffer->delete();
        
        $this->assertTrue($jobOffer->isDeleted());
        $this->assertInstanceOf(\DateTimeImmutable::class, $jobOffer->getDeletedAt());
        
        $jobOffer->restore();
        
        $this->assertFalse($jobOffer->isDeleted());
        $this->assertNull($jobOffer->getDeletedAt());
    }

    /**
     * @test
     */
    public function it_starts_with_empty_applications(): void
    {
        $jobOffer = new JobOffer('Developer', 'Description');

        $this->assertCount(0, $jobOffer->getApplications());
        $this->assertEquals(0, $jobOffer->getApplicationCount());
    }

    /**
     * @test
     */
    public function it_can_update_title(): void
    {
        $jobOffer = new JobOffer('Old Title', 'Description');
        
        $jobOffer->setTitle('New Title');

        $this->assertEquals('New Title', $jobOffer->getTitle());
    }

    /**
     * @test
     */
    public function it_can_update_description(): void
    {
        $jobOffer = new JobOffer('Title', 'Old Description');
        
        $jobOffer->setDescription('New Description');

        $this->assertEquals('New Description', $jobOffer->getDescription());
    }
}
