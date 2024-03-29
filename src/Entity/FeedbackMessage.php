<?php

namespace App\Entity;

use App\Repository\FeedbackMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeedbackMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FeedbackMessage
{
    public const FEEDBACK_TYPE_OTHER = 0;
    public const FEEDBACK_TYPE_BUG = 1;
    public const FEEDBACK_TYPE_BAD_DATA = 2;
    public const FEEDBACK_TYPE_NEW_CLINIC = 3;

    public const FEEDBACK_TYPES = [
        'Report website bug' => self::FEEDBACK_TYPE_BUG,
        'Report issue with listing for clinic/provider' => self::FEEDBACK_TYPE_BAD_DATA,
        'Request to add listing for clinic/provider' => self::FEEDBACK_TYPE_NEW_CLINIC,
        'Other' => self::FEEDBACK_TYPE_OTHER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $feedbackType = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $messageText = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $submittedOn = null;

    #[ORM\ManyToOne]
    private ?Clinic $clinic = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFeedbackType(): ?int
    {
        return $this->feedbackType;
    }

    public function setFeedbackType(int $feedbackType): self
    {
        $this->feedbackType = $feedbackType;

        return $this;
    }

    public function getMessageText(): ?string
    {
        return $this->messageText;
    }

    public function setMessageText(string $messageText): self
    {
        $this->messageText = $messageText;

        return $this;
    }

    public function getSubmittedOn(): ?\DateTimeInterface
    {
        return $this->submittedOn;
    }

    public function setSubmittedOn(\DateTimeInterface $submittedOn): self
    {
        $this->submittedOn = $submittedOn;

        return $this;
    }

    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function setClinic(?Clinic $clinic): self
    {
        $this->clinic = $clinic;

        return $this;
    }

    #[ORM\PrePersist]
    public function onRecordCreate()
    {
        $now = new \DateTime();
        $this->submittedOn = $now;
    }
}
