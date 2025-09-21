<?php

namespace App\Domain\Entry\Message\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

#[Map(Entry::class)]
class CreateOrUpdateEntryCommand implements CommandInterface
{
    public function __construct(
        #[NotNull]
        private ?Account $account = null,
        #[NotBlank]
        private string $name = '',
        #[NotEqualTo(0)]
        private float $amount = 0.0,
        private ?Budget $budget = null,
        /**
         * @var EntryFlagEnum[]
         */
        private array $flags = [],
        #[Map(if: false)]
        private ?Entry $origin = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): CreateOrUpdateEntryCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateEntryCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): CreateOrUpdateEntryCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): CreateOrUpdateEntryCommand
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * @return EntryFlagEnum[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param EntryFlagEnum[] $flags
     */
    public function setFlags(array $flags): CreateOrUpdateEntryCommand
    {
        $this->flags = $flags;

        return $this;
    }

    public function getOrigin(): ?Entry
    {
        return $this->origin;
    }

    public function setOrigin(?Entry $origin): CreateOrUpdateEntryCommand
    {
        $this->origin = $origin;

        return $this;
    }
}
