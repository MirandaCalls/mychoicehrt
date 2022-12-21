<?php

namespace App\Entity;

use App\Repository\DuplicateLinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DuplicateLinkRepository::class)]
class DuplicateLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clinic $clinicA = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clinic $clinicB = null;

    #[ORM\Column]
    private ?float $similarity = null;

    #[ORM\Column]
    private ?bool $dismissed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClinicA(): ?Clinic
    {
        return $this->clinicA;
    }

    public function setClinicA(?Clinic $clinicA): self
    {
        $this->clinicA = $clinicA;

        return $this;
    }

    public function getClinicB(): ?Clinic
    {
        return $this->clinicB;
    }

    public function setClinicB(?Clinic $clinicB): self
    {
        $this->clinicB = $clinicB;

        return $this;
    }

    public function getSimilarity(): ?float
    {
        return $this->similarity;
    }

    public function setSimilarity(float $similarity): self
    {
        $this->similarity = $similarity;

        return $this;
    }

    public function isDismissed(): ?bool
    {
        return $this->dismissed;
    }

    public function setDismissed(bool $dismissed): self
    {
        $this->dismissed = $dismissed;

        return $this;
    }
}
