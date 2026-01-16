<?php

declare(strict_types=1);

namespace App\JobManagement\Domain\Entity;

use App\Identity\Domain\Entity\User;
use App\JobManagement\Infrastructure\Repository\DoctrineJobApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Shared\Domain\Traits\TimestampableTrait;
use App\Shared\Domain\Traits\SoftDeletableTrait;

#[ORM\Entity(repositoryClass: DoctrineJobApplicationRepository::class)]
#[ORM\Table(name: 'job_application')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['user', 'jobOffer'],
    message: 'You have already applied to this job.'
)]
class JobApplication
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['application:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application:read', 'application:write'])]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application:read', 'application:write'])]
    private JobOffer $jobOffer;

    public function __construct(User $user, JobOffer $jobOffer)
    {
        $this->user = $user;
        $this->jobOffer = $jobOffer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getJobOffer(): JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }
}
