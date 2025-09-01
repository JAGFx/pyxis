<?php

namespace App\Domain\PeriodicEntry\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Model\EntryTypeEnum;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Shared\Model\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\When;

#[ORM\Entity(repositoryClass: PeriodicEntryRepository::class)]
class PeriodicEntry
{
    use TimestampableTrait;
    public const int MONTH_SPLIT = 12;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    #[NotBlank]
    private string $name;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[When(
        expression: 'this.isSpent()',
        constraints: [
            new GreaterThan(0.0),
            new NotBlank(),
        ]
    )]
    #[When(
        expression: 'this.isForecast()',
        constraints: [
            new IsNull(),
        ]
    )]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[NotBlank]
    private DateTimeImmutable $executionDate;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\ManyToMany(targetEntity: Budget::class, inversedBy: 'periodicEntries', fetch: 'EXTRA_LAZY')]
    private Collection $budgets;

    #[ManyToOne(inversedBy: 'entries')]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    private ?Account $account = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastExecutionDate = null;

    public function __construct()
    {
        $this->createdAt     = new DateTimeImmutable();
        $this->executionDate = new DateTimeImmutable();
        $this->budgets       = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): PeriodicEntry
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTotalAmount(): float
    {
        if ($this->isSpent()) {
            return $this->amount ?? 0.0;
        }

        $amount = 0.0;

        foreach ($this->getBudgets() as $budget) {
            $budgetAmount = $this->getAmountFor($budget);

            if ($budgetAmount <= 0.0) {
                continue;
            }

            $amount += $budgetAmount;
        }

        return $amount;
    }

    public function getAmountFor(Budget $budgetTarget): float
    {
        if (false === $budgetTarget->getEnable()) {
            return 0.0;
        }

        /** @var ?Budget $budget */
        $budget = $this->budgets->findFirst(fn (int $k, Budget $budget): bool => $budget === $budgetTarget); // @phpstan-ignore-line

        if (is_null($budget)) {
            return 0.0;
        }

        return round($budget->getAmount() / self::MONTH_SPLIT, 2);
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
        if (!$this->budgets->contains($budget)) {
            $this->budgets->add($budget);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): PeriodicEntry
    {
        if ($this->budgets->contains($budget)) {
            $this->budgets->removeElement($budget);
        }

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
}
