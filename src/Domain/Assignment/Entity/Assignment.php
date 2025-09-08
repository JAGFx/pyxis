<?php

namespace App\Domain\Assignment\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Domain\Assignment\Validator\AmountLessOrEqualTotalValueAccount;
use App\Shared\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
#[AmountLessOrEqualTotalValueAccount]
class Assignment
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[NotBlank]
    private string $name;

    #[ORM\Column]
    #[NotBlank]
    #[Positive]
    private float $amount;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private Account $account;

    public function getId(): ?int
    {
        return $this->id;
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
