<?php

namespace App\Shared\DTO;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class Transfer
{
    #[NotNull]
    private ?Account $account = null;

    #[NotBlank(allowNull: true)]
    private ?Budget $budgetSource = null;

    #[NotBlank(allowNull: true)]
    private ?Budget $budgetTarget = null;

    #[NotBlank]
    #[Positive]
    private float $amount = 0;

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): Transfer
    {
        $this->account = $account;

        return $this;
    }

    public function getBudgetSource(): ?Budget
    {
        return $this->budgetSource;
    }

    public function setBudgetSource(?Budget $budgetSource): Transfer
    {
        $this->budgetSource = $budgetSource;

        return $this;
    }

    public function getBudgetTarget(): ?Budget
    {
        return $this->budgetTarget;
    }

    public function setBudgetTarget(?Budget $budgetTarget): Transfer
    {
        $this->budgetTarget = $budgetTarget;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): Transfer
    {
        $this->amount = $amount;

        return $this;
    }
}
