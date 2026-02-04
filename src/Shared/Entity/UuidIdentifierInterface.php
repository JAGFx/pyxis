<?php

namespace App\Shared\Entity;

use Symfony\Component\Uid\Uuid;

interface UuidIdentifierInterface
{
    public function getUuid(): ?Uuid;

    public function setUuid(?Uuid $uuid): self;

    public function generateUuid(): self;
}
