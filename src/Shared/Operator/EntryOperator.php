<?php

namespace App\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\DTO\AssignmentSearchCommand;
use App\Domain\Assignment\Manager\AssignmentManager;
use App\Domain\Entry\DTO\EntrySearchCommand;
use App\Domain\Entry\Manager\EntryManager;
use App\Shared\ValueObject\AmountBalance;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

readonly class EntryOperator
{
    public function __construct(
        private EntryManager $entryManager,
        private AssignmentManager $assignmentManager,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getAmountBalance(?Account $account = null): AmountBalance
    {
        $entryBalance = $this->entryManager->balance(
            is_null($account) ? null : new EntrySearchCommand($account)
        );

        $assignmentsBalance = $this->assignmentManager->balance(
            is_null($account) ? null : new AssignmentSearchCommand($account)
        );

        $totalSpent = $entryBalance->getTotalSpent() - $assignmentsBalance;

        return new AmountBalance(
            $totalSpent + $entryBalance->getTotalForecast() + $assignmentsBalance,
            $totalSpent,
            $entryBalance->getTotalForecast(),
            $assignmentsBalance
        );
    }
}
