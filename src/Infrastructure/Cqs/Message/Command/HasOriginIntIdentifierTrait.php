<?php

namespace App\Infrastructure\Cqs\Message\Command;

use Symfony\Component\ObjectMapper\Attribute\Map;

trait HasOriginIntIdentifierTrait
{
    #[Map(if: false)]
    private ?int $originId = null;

    public function getOriginId(): ?int
    {
        return $this->originId;
    }

    public function setOriginId(?int $origin): self
    {
        $this->originId = $origin;

        return $this;
    }
}
