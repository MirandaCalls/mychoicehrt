<?php

namespace App\Entity;

use App\Repository\ClinicRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Jsor\Doctrine\PostGIS\Types\PostGISType;

#[ORM\Entity(repositoryClass: ClinicRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Clinic
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $dataSource = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\Column(type: PostGISType::GEOGRAPHY)]
    private ?string $location = null;

    #[ORM\Column]
    private ?bool $published = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $importedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedOn = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataSource(): ?string
    {
        return $this->dataSource;
    }

    public function setDataSource(string $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getImportedOn(): ?\DateTimeInterface
    {
        return $this->importedOn;
    }

    public function setImportedOn(\DateTimeInterface $importedOn): self
    {
        $this->importedOn = $importedOn;

        return $this;
    }

    public function getUpdatedOn(): ?\DateTimeInterface
    {
        return $this->updatedOn;
    }

    public function setUpdatedOn(\DateTimeInterface $updatedOn): self
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    #[ORM\PrePersist]
    public function onRecordCreate()
    {
        $now = new \DateTime();
        $this->importedOn = $now;
        $this->updatedOn = $now;
        $this->location = 'POINT(' . $this->longitude . ' ' . $this->latitude . ')';
    }

    #[ORM\PreUpdate]
    public function onRecordUpdate()
    {
        $this->updatedOn = new \DateTime();
        $this->location = 'POINT(' . $this->longitude . ' ' . $this->latitude . ')';
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
