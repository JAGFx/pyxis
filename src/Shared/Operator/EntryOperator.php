<?php

namespace App\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\ValueObject\AmountBalance;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

readonly class EntryOperator
{
    public function __construct(
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function getAmountBalance(?Account $account = null): AmountBalance
    {
        /** @var EntryBalance $entryBalance */
        $entryBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery($account));

        /** @var float $assignmentsBalance */
        $assignmentsBalance = $this->messageBus->dispatch(new GetAssignmentBalanceQuery($account?->getId()));

        $totalSpent = $entryBalance->getTotalSpent() - $assignmentsBalance;

        return new AmountBalance(
            $totalSpent + $entryBalance->getTotalForecast() + $assignmentsBalance,
            $totalSpent,
            $entryBalance->getTotalForecast(),
            $assignmentsBalance
        );
    }
}
