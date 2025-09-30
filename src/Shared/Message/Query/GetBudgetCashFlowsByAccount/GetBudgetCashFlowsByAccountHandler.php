<?php

namespace App\Shared\Message\Query\GetBudgetCashFlowsByAccount;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use ReflectionException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see GetBudgetCashFlowsByAccountQuery
 */
readonly class GetBudgetCashFlowsByAccountHandler implements QueryHandlerInterface
{
    public function __construct(
        private MessageBus $messageBus,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @return BudgetCashFlowByAccountValueObject[]
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function __invoke(GetBudgetCashFlowsByAccountQuery $query): array
    {
        $budget = $this->entityFinder->findByIntIdentifierOrFail(
            Budget::class,
            $query->getBudgetId()
        );

        /** @var Account[] $accounts */
        $accounts = $this->messageBus->dispatch(new FindAccountsQuery());

        $cashFlows = [];
        foreach ($accounts as $account) {
            $cashFlow = $budget->getCashFlow($account);

            if (0.0 === $cashFlow) {
                continue;
            }

            $cashFlows[] = new BudgetCashFlowByAccountValueObject(
                $budget,
                $account,
                $cashFlow
            );
        }

        return $cashFlows;
    }
}
