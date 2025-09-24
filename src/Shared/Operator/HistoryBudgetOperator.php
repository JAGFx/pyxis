<?php

namespace App\Shared\Operator;

use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Message\Command\CreateHistoryBudget\CreateHistoryBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgetVO\FindBudgetVOQuery;
use App\Domain\Budget\Message\Query\FindHistoryBudgets\FindHistoryBudgetsQuery;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Utils\YearRange;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

readonly class HistoryBudgetOperator
{
    public function __construct(
        private BudgetManager $budgetManager,
        private LoggerInterface $logger,
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function generateHistoryBudgetsForYear(int $year): void
    {
        /** @var BudgetValueObject[] $budgetsValues */
        $budgetsValues = $this->messageBus->dispatch(
            new FindBudgetVOQuery()
                ->setShowCredits(false)
                ->setYear($year)
        );

        foreach ($budgetsValues as $budgetValue) {
            $budget = $this->budgetManager->find($budgetValue->getId());

            if (is_null($budget)) {
                continue;
            }

            $historyBudgets = $this->messageBus->dispatch(new FindHistoryBudgetsQuery(
                $budget->getId(),
                $year
            ));

            if ([] !== $historyBudgets) {
                continue;
            }

            $relativeProgress = 0.0 !== $budget->getAmount()
                ? ($budgetValue->getProgress(true) / $budget->getAmount()) * 100
                : 0.0;

            $createCommand = new CreateHistoryBudgetCommand(
                $budget,
                $budget->getAmount(),
                YearRange::firstDayOf($year),
                $budgetValue->getProgress(true),
                $relativeProgress,
            );

            try {
                $this->messageBus->dispatch($createCommand);
            } catch (Throwable $throwable) {
                $this->logger->error($throwable->getMessage(), [
                    'budget_id' => $budget->getId(),
                    'year'      => $year,
                    'exception' => $throwable,
                ]);
            }
        }
    }
}
