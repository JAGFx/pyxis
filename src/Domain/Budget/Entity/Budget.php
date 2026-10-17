<?php

namespace App\Domain\Budget\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Model\BudgetProgressTrait;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Entry\Entity\Entry;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Shared\Entity\HasCollectionTrait;
use App\Shared\Entity\HasIntIdentifierTrait;
use App\Shared\Entity\IntIdentifierInterface;
use App\Shared\Entity\NameableTrait;
use App\Shared\Entity\TimestampableTrait;
use App\Shared\Utils\YearRange;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget implements IntIdentifierInterface
{
    use BudgetProgressTrait;
    use TimestampableTrait;
    use NameableTrait;
    use HasCollectionTrait;
    use HasIntIdentifierTrait;

    /**
     * In cents.
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $amount;

    /**
     * @var Collection<int, PeriodicEntry>
     */
    #[ORM\ManyToMany(targetEntity: PeriodicEntry::class, mappedBy: 'budgets', fetch: 'EXTRA_LAZY')]
    private Collection $periodicEntries;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: Entry::class, cascade: ['persist', 'remove'])]
    private Collection $entries;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $readOnly = false;

    public function getProgress(bool $showAsSpentOnly = false): int
    {
        $progress = array_reduce(
            $this->entries->toArray(),
            static fn (int $currentSum, Entry $entry): int => $currentSum + $entry->getAmount(),
            0
        );

        // Same meaning as BudgetValueObject::getProgress()
        return ($showAsSpentOnly) ? abs($progress) : $progress;
    }

    public function getCashFlow(?Account $account = null): int
    {
        $readableCollection = $this->entries->filter(
            static function (Entry $entry) use ($account): bool {
                if (!is_null($account) && $entry->getAccount() !== $account) {
                    return false;
                }

                if ($entry->getCreatedAt() < YearRange::firstDayOf(YearRange::current())) {
                    return true;
                }

                return $entry->isBalancing();
            }
        );

        return array_reduce(
            $readableCollection->toArray(),
            static fn (int $cashFlow, Entry $entry): int => $cashFlow + $entry->getAmount(),
            0
        );
    }

    public function hasNegativeCashFlow(): bool
    {
        return $this->getCashFlow() < 0;
    }

    public function hasPositiveCashFlow(): bool
    {
        return $this->getCashFlow() > 0;
    }

    /**
     * Balancing is done per account: a zero global cash flow can still hide
     * opposite cash flows on different accounts.
     */
    public function hasCashFlowToBalance(): bool
    {
        $accounts = [];
        foreach ($this->entries as $entry) {
            $account = $entry->getAccount();

            if ($account instanceof Account) {
                $accounts[spl_object_id($account)] = $account;
            }
        }

        return array_any($accounts, fn (Account $account): bool => 0 !== $this->getCashFlow($account));
    }

    public function __construct()
    {
        $this->createdAt       = new DateTimeImmutable();
        $this->periodicEntries = new ArrayCollection();
        $this->entries         = new ArrayCollection();
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

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
}
