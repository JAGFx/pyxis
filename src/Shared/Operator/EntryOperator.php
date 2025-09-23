<?php

namespace App\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Message\Query\GetEntryBalanceQuery;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\ValueObject\AmountBalance;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

readonly class EntryOperator
{
    public function __construct(
        private EntryManager $entryManager,
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function getAmountBalance(?Account $account = null): AmountBalance
    {
        $entryBalance = $this->entryManager->balance(
            new GetEntryBalanceQuery($account)
        );

        /** @var float $assignmentsBalance */
        $assignmentsBalance = $this->messageBus->dispatch(new GetAssignmentBalanceQuery($account));

        $totalSpent = $entryBalance->getTotalSpent() - $assignmentsBalance;

        return new AmountBalance(
            $totalSpent + $entryBalance->getTotalForecast() + $assignmentsBalance,
            $totalSpent,
            $entryBalance->getTotalForecast(),
            $assignmentsBalance
        );
    }
}
