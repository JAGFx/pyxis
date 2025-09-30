<?php

namespace App\Shared\Operator;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Message\Query\FindBudgetVO\FindBudgetVOQuery;
use App\Domain\Budget\Message\Query\FindHistoryBudgets\FindHistoryBudgetsQuery;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Utils\YearRange;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

readonly class BudgetOperator
{
    public function __construct(
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @return BudgetBalanceProgressValueObject[]
     *
     * @throws ExceptionInterface
     */
    public function getBudgetBalanceProgresses(FindBudgetsQuery $searchQuery): array
    {
        if (YearRange::current() === $searchQuery->getYear()) {
            /** @var BudgetValueObject[] $budgetsValues */
            $budgetsValues = $this->messageBus->dispatch(
                new FindBudgetVOQuery()
                    ->setShowCredits($searchQuery->getShowCredits())
                    ->setYear(YearRange::current())
            );

            return array_map(
                fn (BudgetValueObject $budgetValueObject): BudgetBalanceProgressValueObject => new BudgetBalanceProgressValueObject(
                    $budgetValueObject->getName(),
                    $budgetValueObject->getProgress(true),
                    $budgetValueObject->getStatus(true),
                    $budgetValueObject->getAmount(),
                    $budgetValueObject->getRelativeProgress(true)
                ),
                $budgetsValues
            );
        }

        /** @var HistoryBudget[] $histories */
        $histories = $this->messageBus->dispatch(new FindHistoryBudgetsQuery(year: $searchQuery->getYear()));

        return array_map(
            fn (HistoryBudget $history): BudgetBalanceProgressValueObject => new BudgetBalanceProgressValueObject(
                $history->getBudget()?->getName(),
                $history->getSpent() ?? 0.0,
                $history->getStatus(),
                $history->getAmount(),
                $history->getRelativeProgress()
            ),
            $histories
        );
    }
}
