<?php

namespace App\Shared\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait NameableTrait
{
    #[Column(type: Types::STRING)]
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
