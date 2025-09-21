<?php

namespace App\Domain\Budget\Message\Command;

use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

#[Map(Budget::class)]
class BudgetCreateOrUpdateCommand implements CommandInterface
{
    public function __construct(
        #[NotBlank]
        public string $name = '',

        #[Positive]
        public float $amount = 0.0,

        public bool $enabled = true,

        #[Map(if: false)]
        private ?Budget $origin = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): BudgetCreateOrUpdateCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): BudgetCreateOrUpdateCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): BudgetCreateOrUpdateCommand
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getOrigin(): ?Budget
    {
        return $this->origin;
    }

    public function setOrigin(?Budget $origin): BudgetCreateOrUpdateCommand
    {
        $this->origin = $origin;

        return $this;
    }
}
