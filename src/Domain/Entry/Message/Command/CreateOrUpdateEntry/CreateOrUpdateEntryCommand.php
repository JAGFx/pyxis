<?php

namespace App\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Validator\HasEnoughAmountAssignment;
use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Infrastructure\Cqs\Validation\ValidationGroupEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @see CreateOrUpdateEntryHandler
 */
#[Map(Entry::class)]
#[HasEnoughAmountAssignment(groups: [ValidationGroupEnum::Business->value])]
class CreateOrUpdateEntryCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;

    public function __construct(
        #[NotNull]
        private ?Account $account = null,

        #[NotBlank]
        private ?string $name = null,

        #[NotEqualTo(0)]
        private float $amount = 0.0,

        private ?Budget $budget = null,

        /**
         * @var EntryFlagEnum[]
         */
        private array $flags = [],

        #[Map(if: false)]
        private ?Assignment $assignment = null,
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CreateOrUpdateEntryCommand
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

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): CreateOrUpdateEntryCommand
    {
        $this->assignment = $assignment;

        return $this;
    }
}
