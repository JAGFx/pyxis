<?php

namespace App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Infrastructure\Cqs\Validation\ValidationGroupEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Entity\HasCollectionTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\When;

/**
 * @see CreateOrUpdatePeriodicEntryHandler
 */
#[Map(PeriodicEntry::class)]
#[Expression(
    expression: 'this.getAssignment() === null or this.getAccount() === null or this.getAssignment().getAccount().getId() === this.getAccount().getId()',
    message: 'periodic_entry.create_or_update.assignment_account_mismatch',
    groups: [ValidationGroupEnum::Business->value]
)]
class CreateOrUpdatePeriodicEntryCommand implements CommandInterface
{
    use HasCollectionTrait;
    use HasOriginIntIdentifierTrait;

    public function __construct(
        #[NotNull]
        private ?Account $account = null,

        #[NotBlank]
        private ?string $name = '',

        #[When(
            expression: 'this.isSpent()',
            constraints: [
                new GreaterThan(0.0),
                new NotNull(),
            ],
            groups: [ValidationGroupEnum::Business->value]
        )]
        #[When(
            expression: 'this.isForecast()',
            constraints: [
                new IsNull(),
            ],
            groups: [ValidationGroupEnum::Business->value]
        )]
        private ?float $amount = null,

        #[NotNull]
        private ?DateTimeImmutable $executionDate = null,

        /**
         * @var Collection<int, Budget>
         */
        private Collection $budgets = new ArrayCollection(),

        #[When(
            expression: 'this.isForecast() == true',
            constraints: [
                new IsNull(message: 'periodic_entry.create_or_update.assignment_forbidden_with_budgets'),
            ],
            groups: [ValidationGroupEnum::Business->value]
        )]
        private ?Assignment $assignment = null,
    ) {
    }

    public function getType(): EntryTypeEnum
    {
        return $this->budgets->isEmpty() && !is_null($this->amount)
            ? EntryTypeEnum::TYPE_SPENT
            : EntryTypeEnum::TYPE_FORECAST;
    }

    public function isForecast(): bool
    {
        return EntryTypeEnum::TYPE_FORECAST === $this->getType();
    }

    public function isSpent(): bool
    {
        return EntryTypeEnum::TYPE_SPENT === $this->getType();
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): CreateOrUpdatePeriodicEntryCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CreateOrUpdatePeriodicEntryCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): CreateOrUpdatePeriodicEntryCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExecutionDate(): ?DateTimeImmutable
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?DateTimeImmutable $executionDate): CreateOrUpdatePeriodicEntryCommand
    {
        $this->executionDate = $executionDate;

        return $this;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    /**
     * @param Collection<int, Budget> $budgets
     */
    public function setBudgets(Collection $budgets): CreateOrUpdatePeriodicEntryCommand
    {
        $this->budgets = $budgets;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): CreateOrUpdatePeriodicEntryCommand
    {
        $this->assignment = $assignment;

        return $this;
    }
}
