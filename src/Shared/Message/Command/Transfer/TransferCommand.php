<?php

namespace App\Shared\Message\Command\Transfer;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @see TransferHandler
 */
class TransferCommand implements CommandInterface
{
    #[NotNull]
    private ?Account $account     = null;

    private ?Budget $budgetSource = null;

    private ?Budget $budgetTarget = null;

    #[NotBlank]
    #[Positive]
    private float $amount = 0;

    #[Assert\Callback]
    public function validate(ExecutionContext $context): void
    {
        if (!is_null($this->budgetSource) && !is_null($this->budgetTarget) && $this->budgetSource === $this->budgetTarget) {
            $context->buildViolation('shared.transfer.same_budget_source_target')
                ->atPath('budgetTarget')
                ->addViolation();
        }
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): TransferCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getBudgetSource(): ?Budget
    {
        return $this->budgetSource;
    }

    public function setBudgetSource(?Budget $budgetSource): TransferCommand
    {
        $this->budgetSource = $budgetSource;

        return $this;
    }

    public function getBudgetTarget(): ?Budget
    {
        return $this->budgetTarget;
    }

    public function setBudgetTarget(?Budget $budgetTarget): TransferCommand
    {
        $this->budgetTarget = $budgetTarget;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): TransferCommand
    {
        $this->amount = $amount;

        return $this;
    }
}
