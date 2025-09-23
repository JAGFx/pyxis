<?php

namespace App\Domain\Entry\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Repository\EntryRepository;
use App\Shared\Entity\EntityCollectionTrait;
use App\Shared\Entity\EntityIntIdentifierInterface;
use App\Shared\Entity\EntityIntIdentifierTrait;
use App\Shared\Entity\NameableTrait;
use App\Shared\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: EntryRepository::class)]
class Entry implements EntityIntIdentifierInterface
{
    use TimestampableTrait;
    use NameableTrait;
    use EntityCollectionTrait;
    use EntityIntIdentifierTrait;
    public const array NON_EDITABLE_FLAGS = [
        EntryFlagEnum::BALANCE,
        EntryFlagEnum::TRANSFERT,
        EntryFlagEnum::PERIODIC_ENTRY,
        EntryFlagEnum::HIDDEN,
    ];

    #[Column(type: Types::FLOAT)]
    private float $amount = 0;

    #[ManyToOne(fetch: 'EXTRA_LAZY', inversedBy: 'entries')]
    private ?Budget $budget = null;

    #[ManyToOne(inversedBy: 'entries')]
    #[JoinColumn(nullable: false)]
    private ?Account $account = null;

    /** @var EntryFlagEnum[] */
    #[Column(type: Types::JSON, enumType: EntryFlagEnum::class)]
    private array $flags = [];

    public function getType(): EntryTypeEnum
    {
        return ($this->budget instanceof Budget)
            ? EntryTypeEnum::TYPE_FORECAST
            : EntryTypeEnum::TYPE_SPENT;
    }

    public function isForecast(): bool
    {
        return EntryTypeEnum::TYPE_FORECAST === $this->getType();
    }

    public function isSpent(): bool
    {
        return EntryTypeEnum::TYPE_SPENT === $this->getType();
    }

    public function isEditable(): bool
    {
        if ([] === $this->flags) {
            return true;
        }

        return array_all(self::NON_EDITABLE_FLAGS, fn (EntryFlagEnum $nonEditableFlag): bool => !in_array($nonEditableFlag, $this->flags, true));
    }

    public function isBalancing(): bool
    {
        return in_array(EntryFlagEnum::BALANCE, $this->flags ?? [], true);
    }

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        if ($budget instanceof Budget) {
            $budget->addEntry($this);
        }

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): Entry
    {
        $this->account = $account;

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
    public function setFlags(array $flags): Entry
    {
        $this->flags = $flags;

        return $this;
    }

    public function addFlag(EntryFlagEnum $flag): self
    {
        if (!in_array($flag, $this->flags, true)) {
            $this->flags[] = $flag;
        }

        return $this;
    }
}
