<?php

namespace App\Shared\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

trait HasUuidIdentifierTrait
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $uuid = null;

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): self
    {
        if (!is_null($uuid)) {
            return $this;
        }

        $this->uuid = $uuid;

        return $this;
    }

    #[ORM\PrePersist]
    public function generateUuid(): self
    {
        if (!is_null($this->uuid)) {
            return $this;
        }

        $this->uuid = Uuid::v7();

        return $this;
    }
}
