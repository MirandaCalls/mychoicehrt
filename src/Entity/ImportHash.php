<?php

namespace App\Entity;

use App\Repository\ImportHashRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportHashRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ImportHash
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $dataSource = null;

    #[ORM\Column(length: 32)]
    private ?string $hash = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $importedOn = null;

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

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    #[ORM\PrePersist]
    public function onRecordCreate()
    {
        $this->importedOn = new \DateTime();
    }
}
