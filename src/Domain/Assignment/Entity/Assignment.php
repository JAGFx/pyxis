<?php

namespace App\Domain\Assignment\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Shared\Entity\EntityIntIdentifierInterface;
use App\Shared\Entity\EntityIntIdentifierTrait;
use App\Shared\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
class Assignment implements EntityIntIdentifierInterface
{
    use TimestampableTrait;
    use EntityIntIdentifierTrait;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private float $amount;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

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
