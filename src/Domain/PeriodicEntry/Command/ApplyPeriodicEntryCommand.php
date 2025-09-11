<?php

namespace App\Domain\PeriodicEntry\Command;

use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use App\Shared\Operator\PeriodicEntryOperator;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Throwable;

#[AsCommand('bugr:periodic-entry:apply')]
#[AsPeriodicTask('P1D', '14:00:00')]
class ApplyPeriodicEntryCommand
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PeriodicEntryOperator $periodicEntryOperator,
        private readonly PeriodicEntryManager $periodicEntryManager,
    ) {
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            $periodicEntries = $this->periodicEntryManager->getPeriodicEntries();

            foreach ($periodicEntries as $periodicEntry) {
                try {
                    $this->periodicEntryOperator->addSplitForBudgets($periodicEntry);
                    $periodicEntry->setLastExecutionDate(new DateTimeImmutable());

                    $this->periodicEntryManager->update($periodicEntry);
                } catch (PeriodicEntrySplitBudgetException $exception) {
                    $symfonyStyle->info($exception->getMessage());
                    $this->logger->info($exception->getMessage(), [
                        '$periodicEntry' => $periodicEntry->getId(),
                    ]);
                }
            }

            $symfonyStyle->success('The job has been executed successfully.');

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
