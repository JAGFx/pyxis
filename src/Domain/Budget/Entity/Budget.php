<?php

namespace App\Domain\Budget\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Model\BudgetProgressTrait;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Entry\Entity\Entry;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Shared\Entity\CollectionManagerTrait;
use App\Shared\Entity\NameableTrait;
use App\Shared\Entity\TimestampableTrait;
use App\Shared\Utils\YearRange;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThan;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    use BudgetProgressTrait;
    use TimestampableTrait;
    use NameableTrait;
    use CollectionManagerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[GreaterThan(value: 0)]
    private float $amount;

    /**
     * @var Collection<int, PeriodicEntry>
     */
    #[ORM\ManyToMany(targetEntity: PeriodicEntry::class, mappedBy: 'budgets', fetch: 'EXTRA_LAZY')]
    private Collection $periodicEntries;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: Entry::class, cascade: ['persist', 'remove'], indexBy: 'createdAt')]
    private Collection $entries;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $readOnly = false;

    public function __construct()
    {
        $this->createdAt       = new DateTimeImmutable();
        $this->periodicEntries = new ArrayCollection();
        $this->entries         = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = round($amount, 2);

        return $this;
    }

    /**
     * @return Collection<int, PeriodicEntry>
     */
    public function getPeriodicEntries(): Collection
    {
        return $this->periodicEntries;
    }

    public function addPeriodicEntry(PeriodicEntry $periodicEntry): self
    {
        $this->addToCollection($this->periodicEntries, $periodicEntry, 'addBudget', $this);

        return $this;
    }

    public function removePeriodicEntry(PeriodicEntry $periodicEntry): self
    {
        $this->removeFromCollection($this->periodicEntries, $periodicEntry, 'removeBudget', $this);

        return $this;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    /**
     * @param Collection<int, Entry> $entries
     */
    public function setEntries(Collection $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function addEntry(Entry $entry): self
    {
        $this->addToCollection($this->entries, $entry, 'setBudget', $this);

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        $this->removeFromCollection($this->entries, $entry, 'setBudget');

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function getProgress(bool $showAsSpentOnly = false): float
    {
        return array_reduce(
            $this->entries->toArray(),
            static fn (float $currentSum, Entry $entry): float => $currentSum + (($showAsSpentOnly) ? 0 : $entry->getAmount()),
            0
        );
    }

    public function getCashFlow(?Account $account = null): float
    {
        $readableCollection = $this->entries->filter(
            static function (Entry $entry) use ($account): bool {
                if (!is_null($account) && $entry->getAccount() !== $account) {
                    return false;
                }

                if ($entry->getCreatedAt() < YearRange::firstDayOf(YearRange::current())) {
                    return true;
                }

                return $entry->isABalancing();
            }
        );

        return array_reduce(
            $readableCollection->toArray(),
            static fn (float $cashFlow, Entry $entry): float => $cashFlow + $entry->getAmount(),
            0.0
        );
    }

    public function hasNegativeCashFlow(): bool
    {
        return round($this->getCashFlow(), 2) < 0.0;
    }

    public function hasPositiveCashFlow(): bool
    {
        return round($this->getCashFlow(), 2) > 0.0;
    }
}
