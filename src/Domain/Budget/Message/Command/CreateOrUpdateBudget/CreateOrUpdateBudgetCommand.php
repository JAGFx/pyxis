<?php

namespace App\Domain\Budget\Message\Command\CreateOrUpdateBudget;

use App\Domain\Budget\Entity\Budget;
use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * @see CreateOrUpdateBudgetHandler
 */
#[Map(Budget::class)]
class CreateOrUpdateBudgetCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;

    public function __construct(
        #[NotBlank]
        public string $name = '',

        #[Positive]
        public float $amount = 0.0,

        public bool $enabled = true,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateBudgetCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): CreateOrUpdateBudgetCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): CreateOrUpdateBudgetCommand
    {
        $this->enabled = $enabled;

        return $this;
    }
}
