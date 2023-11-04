<?php

namespace App\Entity;

use App\Repository\GeoCityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeoCityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GeoCity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 200)]
    private ?string $asciiName = null;

    #[ORM\Column(length: 10000)]
    private ?string $alternateNames = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 2)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 20)]
    private ?string $datasetVersion = null;

    #[ORM\Column(type: 'geography')]
    private ?string $location = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAsciiName(): ?string
    {
        return $this->asciiName;
    }

    public function setAsciiName(string $asciiName): self
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    public function getAlternateNames(): ?string
    {
        return $this->alternateNames;
    }

    public function setAlternateNames(string $alternateNames): self
    {
        $this->alternateNames = $alternateNames;

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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getDatasetVersion(): ?string
    {
        return $this->datasetVersion;
    }

    public function setDatasetVersion(string $datasetVersion): self
    {
        $this->datasetVersion = $datasetVersion;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    #[ORM\PrePersist]
    public function onRecordCreate(): void
    {
        $this->location = 'POINT(' . $this->longitude . ' ' . $this->latitude . ')';
    }

    public function toString(): string
    {
        return $this->name . ', ' . $this->state . ' ' . $this->countryCode;
    }
}
