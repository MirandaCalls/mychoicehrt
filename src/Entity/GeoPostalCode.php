<?php

namespace App\Entity;

use App\Repository\GeoPostalCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeoPostalCodeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GeoPostalCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 180)]
    private ?string $placeName = null;

    #[ORM\Column(length: 100)]
    private ?string $state = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\Column(length: 20)]
    private ?string $datasetVersion = null;

    #[ORM\Column(type: 'geography')]
    private ?string $location = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPlaceName(): ?string
    {
        return $this->placeName;
    }

    public function setPlaceName(string $placeName): self
    {
        $this->placeName = $placeName;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

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

}
