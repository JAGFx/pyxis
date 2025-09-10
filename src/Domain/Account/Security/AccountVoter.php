<?php

namespace App\Domain\Account\Security;

use App\Domain\Account\Entity\Account;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Account>
 */
class AccountVoter extends Voter
{
    public const string DISABLE = 'ACCOUNT_DISABLE';
    public const string ENABLE  = 'ACCOUNT_ENABLE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::DISABLE, self::ENABLE], true)) {
            return false;
        }

        return $subject instanceof Account;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Account $account */
        $account = $subject;

        return match ($attribute) {
            self::DISABLE => $this->canDisable($account),
            self::ENABLE  => $this->canEnable($account),
            default       => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canDisable(Account $account): bool
    {
        if (!$account->isEnable()) {
            return false;
        }

        if (!$account->getEntries()->isEmpty()) {
            return false;
        }

        return $account->getAssignments()->isEmpty();
    }

    private function canEnable(Account $account): bool
    {
        return !$account->isEnable();
    }
}
