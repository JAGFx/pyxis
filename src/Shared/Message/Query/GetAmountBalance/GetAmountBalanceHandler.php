<?php

namespace App\Shared\Message\Query\GetAmountBalance;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use App\Shared\ValueObject\AmountBalance;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see GetAmountBalanceQuery
 */
readonly class GetAmountBalanceHandler implements QueryHandlerInterface
{
    public function __construct(
        private MessageBus $messageBus,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @return array<AmountBalance>
     *
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function __invoke(GetAmountBalanceQuery $command): array
    {
        /** @var array<int> $accountsIds */
        $accountsIds    = array_filter($command->getAccountsId());
        $amountBalances = [];

        if ([] === $accountsIds) {
            $amountBalances[] = $this->getBalance(null);

            return $amountBalances;
        }

        foreach ($accountsIds as $accountId) {
            $amountBalances[] = $this->getBalance($accountId);
        }

        return $amountBalances;
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    private function getBalance(?int $accountId): AmountBalance
    {
        $account = $this->entityFinder->findByIntIdentifier(
            Account::class,
            $accountId
        );

        /** @var EntryBalance $entryBalance */
        $entryBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery($account?->getId()));

        /** @var float $assignmentsBalance */
        $assignmentsBalance = $this->messageBus->dispatch(new GetAssignmentBalanceQuery($account?->getId()));

        $totalSpent = $entryBalance->getTotalSpent() - $assignmentsBalance;

        return new AmountBalance(
            $totalSpent + $entryBalance->getTotalForecast() + $assignmentsBalance,
            $totalSpent,
            $entryBalance->getTotalForecast(),
            $assignmentsBalance,
            $account?->getName()
        );
    }
}
