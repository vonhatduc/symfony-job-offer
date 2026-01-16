<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Entity;

use App\JobManagement\Infrastructure\Repository\DoctrineJobOfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Shared\Domain\Traits\TimestampableTrait;
use App\Shared\Domain\Traits\SoftDeletableTrait;

#[ORM\Entity(repositoryClass: DoctrineJobOfferRepository::class)]
#[ORM\Table(name: 'job_offer')]
#[ORM\Index(columns: ['title'], name: 'idx_job_title')]
#[ORM\Index(columns: ['company_name'], name: 'idx_job_company')]
#[ORM\Index(columns: ['status'], name: 'idx_job_status')]
#[ORM\HasLifecycleCallbacks]
class JobOffer
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['job:read', 'application:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['job:read', 'job:write'])]
    private ?string $companyName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['job:read', 'job:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['job:read', 'job:write'])]
    private ?string $salaryRange = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['job:read', 'job:write'])]
    private ?string $employmentType = null;

    #[ORM\Column(length: 20)]
    #[Groups(['job:read', 'job:write'])]
    private string $status = 'active';

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['job:read', 'job:write'])]
    private ?array $requirements = [];

    #[ORM\OneToMany(mappedBy: 'jobOffer', targetEntity: JobApplication::class, orphanRemoval: true)]
    private Collection $applications;

    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;
        $this->applications = new ArrayCollection();
        // Traits will handle createdAt/updatedAt via LifecycleCallbacks
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getSalaryRange(): ?string
    {
        return $this->salaryRange;
    }

    public function setSalaryRange(?string $salaryRange): self
    {
        $this->salaryRange = $salaryRange;
        return $this;
    }

    public function getEmploymentType(): ?string
    {
        return $this->employmentType;
    }

    public function setEmploymentType(?string $employmentType): self
    {
        $this->employmentType = $employmentType;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRequirements(): ?array
    {
        return $this->requirements;
    }

    public function setRequirements(?array $requirements): self
    {
        $this->requirements = $requirements;
        return $this;
    }

    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function getApplicationCount(): int
    {
        return $this->applications->count();
    }

    public function addApplication(JobApplication $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setJobOffer($this);
        }
        return $this;
    }
}
