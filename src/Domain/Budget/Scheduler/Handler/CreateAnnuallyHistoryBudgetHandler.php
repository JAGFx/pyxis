<?php

namespace App\Domain\Budget\Scheduler\Handler;

use App\Domain\Budget\Scheduler\Message\CreateAnnuallyHistoryBudgetMessage;
use App\Shared\Operator\HistoryBudgetOperator;
use App\Shared\Utils\YearRange;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateAnnuallyHistoryBudgetHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private HistoryBudgetOperator $historyBudgetOperator,
    ) {
    }

    public function __invoke(CreateAnnuallyHistoryBudgetMessage $message): void
    {
        $this->logger->info('Creating annually history budgets for year {year}', ['year' => YearRange::current()]);
        $this->historyBudgetOperator->generateHistoryBudgetsForYear(YearRange::current());
    }
}
