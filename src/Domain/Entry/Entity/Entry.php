<?php

namespace App\Domain\Entry\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Repository\EntryRepository;
use App\Shared\Entity\CollectionManagerTrait;
use App\Shared\Entity\NameableTrait;
use App\Shared\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: EntryRepository::class)]
class Entry
{
    use TimestampableTrait;
    use NameableTrait;
    use CollectionManagerTrait;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Column(type: Types::FLOAT)]
    #[NotBlank]
    #[NotEqualTo(0)]
    private float $amount = 0;

    #[ManyToOne(fetch: 'EXTRA_LAZY', inversedBy: 'entries')]
    #[NotBlank(allowNull: true)]
    private ?Budget $budget = null;

    #[Column(enumType: EntryKindEnum::class, options: ['default' => EntryKindEnum::DEFAULT])]
    #[NotBlank]
    private EntryKindEnum $kind = EntryKindEnum::DEFAULT;

    #[ManyToOne(inversedBy: 'entries')]
    #[JoinColumn(nullable: false)]
    #[NotNull]
    private ?Account $account = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getKind(): EntryKindEnum
    {
        return $this->kind;
    }

    public function setKind(EntryKindEnum $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function isABalancing(): bool
    {
        return EntryKindEnum::BALANCING === $this->kind;
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
}
