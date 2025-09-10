<?php

namespace App\Domain\Budget\Security;

use App\Domain\Budget\Entity\Budget;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Budget>
 */
class BudgetVoter extends Voter
{
    public const string MANAGE  = 'BUDGET_MANAGE';
    public const string BALANCE = 'BUDGET_BALANCE';
    public const string ENABLE  = 'BUDGET_ENABLE';
    public const string DISABLE = 'BUDGET_DISABLE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::MANAGE, self::BALANCE, self::ENABLE, self::DISABLE], true)) {
            return false;
        }

        return $subject instanceof Budget;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Budget $budget */
        $budget = $subject;

        return match ($attribute) {
            self::MANAGE  => $this->canManage($budget),
            self::BALANCE => $this->canBalance($budget),
            self::ENABLE  => $this->canEnable($budget),
            self::DISABLE => $this->canDisable($budget),
            default       => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canManage(Budget $budget): bool
    {
        return !$budget->isReadOnly();
    }

    private function canBalance(Budget $budget): bool
    {
        if (!$this->canManage($budget)) {
            return false;
        }
        if ($budget->hasPositiveCashFlow()) {
            return true;
        }

        return $budget->hasNegativeCashFlow();
    }

    private function canEnable(Budget $budget): bool
    {
        return $this->canManage($budget) && !$budget->getEnable();
    }

    private function canDisable(Budget $budget): bool
    {
        return $this->canManage($budget) && $budget->getEnable();
    }
}
