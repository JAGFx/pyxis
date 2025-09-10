<?php

namespace App\Domain\Entry\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use App\Domain\Entry\Entity\Entry;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Entry>
 */
class EntryVoter extends Voter
{
    public const string MANAGE = 'ENTRY_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute !== self::MANAGE) {
            return false;
        }

        return $subject instanceof Entry;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Entry $entry */
        $entry = $subject;

        return match ($attribute) {
            self::MANAGE => $this->canManage($entry),
            default      => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canManage(Entry $entry): bool
    {
        return !$entry->isABalancing();
    }
}
