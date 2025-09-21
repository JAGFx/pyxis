<?php

namespace App\Domain\Account\Message\Command;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Map(Account::class)]
class CreateOrUpdateAccountCommand implements CommandInterface
{
    public function __construct(
        #[NotBlank]
        private string $name = '',

        #[Map(if: false)]
        private ?Account $origin = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateAccountCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getOrigin(): ?Account
    {
        return $this->origin;
    }

    public function setOrigin(?Account $origin): CreateOrUpdateAccountCommand
    {
        $this->origin = $origin;

        return $this;
    }
}
