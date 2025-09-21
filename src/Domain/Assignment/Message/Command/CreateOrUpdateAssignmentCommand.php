<?php

namespace App\Domain\Assignment\Message\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[Map(Assignment::class)]
class CreateOrUpdateAssignmentCommand implements CommandInterface
{
    public function __construct(
        #[NotBlank]
        private string $name = '',
        #[NotBlank]
        #[Positive]
        private float $amount = 0.0,
        #[NotNull]
        private ?Account $account = null,
        #[Map(if: false)]
        private ?Assignment $origin = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateAssignmentCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): CreateOrUpdateAssignmentCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): CreateOrUpdateAssignmentCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getOrigin(): ?Assignment
    {
        return $this->origin;
    }

    public function setOrigin(?Assignment $origin): CreateOrUpdateAssignmentCommand
    {
        $this->origin = $origin;

        return $this;
    }
}
