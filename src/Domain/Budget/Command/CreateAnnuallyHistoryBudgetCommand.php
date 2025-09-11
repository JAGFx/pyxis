<?php

declare(strict_types=1);

namespace App\Domain\Budget\Command;

use App\Shared\Operator\HistoryBudgetOperator;
use App\Shared\Utils\YearRange;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Throwable;

#[AsCommand('bugr:create_annually_history_budget')]
#[AsPeriodicTask('P1Y', 'first day of january 01:00:00')]
class CreateAnnuallyHistoryBudgetCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HistoryBudgetOperator $historyBudgetOperator,
    ) {
        parent::__construct();
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            $this->logger->info('Creating annually history budgets for year {year}', ['year' => YearRange::current() - 1]);
            $this->historyBudgetOperator->generateHistoryBudgetsForYear(YearRange::current() - 1);

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $symfonyStyle->error($throwable->getMessage());
            $this->logger->error('Periodic entry command: Unable to execute the job', [
                'exceptionMessage' => $throwable->getMessage(),
                'exceptionClass'   => $throwable::class,
            ]);

            return Command::FAILURE;
        }
    }
}
