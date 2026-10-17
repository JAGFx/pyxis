<?php

namespace App\Domain\Assignment\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Domain\Entry\Entity\Entry;
use App\Shared\Entity\HasIntIdentifierTrait;
use App\Shared\Entity\IntIdentifierInterface;
use App\Shared\Entity\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
class Assignment implements IntIdentifierInterface
{
    use TimestampableTrait;
    use HasIntIdentifierTrait;

    #[ORM\Column]
    private string $name;

    /**
     * Starting amount: money already set aside when the assignment was created (0 for an envelope filled over time).
     */
    #[ORM\Column]
    private float $amount;

    /**
     * Optional amount to reach, only used for display.
     */
    #[ORM\Column(nullable: true)]
    private ?float $target = null;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(mappedBy: 'assignment', targetEntity: Entry::class, fetch: 'EXTRA_LAZY')]
    private Collection $entries;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    /**
     * Starting amount plus the linked entries: deleting or updating an entry is reflected without any write here.
     */
    public function getBalance(): float
    {
        return round(array_reduce(
            $this->entries->toArray(),
            static fn (float $balance, Entry $entry): float => $balance + ($entry->getAmount() ?? 0.0),
            $this->amount
        ), 2);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Assignment
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): Assignment
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTarget(): ?float
    {
        return $this->target;
    }

    public function setTarget(?float $target): Assignment
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): Assignment
    {
        $this->account = $account;

        return $this;
    }
}
