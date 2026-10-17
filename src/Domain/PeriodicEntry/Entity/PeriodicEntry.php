<?php

namespace App\Domain\PeriodicEntry\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Shared\Entity\HasCollectionTrait;
use App\Shared\Entity\HasIntIdentifierTrait;
use App\Shared\Entity\IntIdentifierInterface;
use App\Shared\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

// TODO: Add a suspend feature
#[ORM\Entity(repositoryClass: PeriodicEntryRepository::class)]
class PeriodicEntry implements IntIdentifierInterface
{
    use TimestampableTrait;
    use HasCollectionTrait;
    use HasIntIdentifierTrait;

    public const int MONTH_SPLIT = 12;

    #[ORM\Column]
    private string $name;

    /**
     * In cents.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $amount = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $executionDate;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\ManyToMany(targetEntity: Budget::class, inversedBy: 'periodicEntries', fetch: 'EXTRA_LAZY')]
    private Collection $budgets;

    #[ManyToOne(inversedBy: 'entries')]
    #[JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastExecutionDate = null;

    #[ManyToOne]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Assignment $assignment = null;

    public function getTotalAmount(): int
    {
        if ($this->isSpent()) {
            return $this->amount ?? 0;
        }

        $amount = 0;

        foreach ($this->getBudgets() as $budget) {
            $budgetAmount = $this->getAmountFor($budget);

            if ($budgetAmount <= 0) {
                continue;
            }

            $amount += $budgetAmount;
        }

        return $amount;
    }

    /**
     * Monthly share of the budget, in cents. The remainder of the division by 12 adds one cent to the first months
     * (100 € = 8,34 € from January to April, then 8,33 €): the 12 shares always sum up to the budget amount.
     * Without a month, the base share is returned (display).
     */
    public function getAmountFor(Budget $budgetTarget, ?int $month = null): int
    {
        if (false === $budgetTarget->isEnabled()) {
            return 0;
        }

        /** @var ?Budget $budget */
        $budget = $this->budgets->findFirst(fn (int $k, Budget $budget): bool => $budget === $budgetTarget); // @phpstan-ignore-line

        if (is_null($budget)) {
            return 0;
        }

        $monthlyAmount = intdiv($budget->getAmount(), self::MONTH_SPLIT);
        $remainder     = $budget->getAmount() % self::MONTH_SPLIT;

        return (!is_null($month) && $month <= $remainder)
            ? $monthlyAmount + 1
            : $monthlyAmount;
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

    public function countBudgets(): ?int
    {
        return $this->budgets->count();
    }

    public function __construct()
    {
        $this->createdAt     = new DateTimeImmutable();
        $this->executionDate = new DateTimeImmutable();
        $this->budgets       = new ArrayCollection();
    }

    public function setId(?int $id): PeriodicEntry
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): PeriodicEntry
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): PeriodicEntry
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExecutionDate(): DateTimeImmutable
    {
        return $this->executionDate;
    }

    public function setExecutionDate(DateTimeImmutable $executionDate): PeriodicEntry
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
    public function setBudgets(Collection $budgets): PeriodicEntry
    {
        $this->budgets = $budgets;

        return $this;
    }

    public function addBudget(Budget $budget): PeriodicEntry
    {
        $this->addToCollection($this->budgets, $budget, null, $this);

        return $this;
    }

    public function removeBudget(Budget $budget): PeriodicEntry
    {
        $this->removeFromCollection($this->budgets, $budget);

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): PeriodicEntry
    {
        $this->account = $account;

        return $this;
    }

    public function getLastExecutionDate(): ?DateTimeImmutable
    {
        return $this->lastExecutionDate;
    }

    public function setLastExecutionDate(?DateTimeImmutable $lastExecutionDate): PeriodicEntry
    {
        $this->lastExecutionDate = $lastExecutionDate;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): PeriodicEntry
    {
        $this->assignment = $assignment;

        return $this;
    }
}
