<?php

namespace App\Domain\PeriodicEntry\Command;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries\FindPeriodicEntriesQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\AddSplitForBudgets\AddSplitForBudgetsCommand;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Throwable;

#[AsCommand('bugr:periodic-entry:apply')]
#[AsPeriodicTask('P1D', '14:00:00')]
readonly class ApplyPeriodicEntryConsoleCommand
{
    public function __construct(
        private LoggerInterface $logger,
        private MessageBus $messageBus,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option]
        ?string $targetDate = null,
    ): int {
        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            $date = (is_null($targetDate)) ? null : new DateTimeImmutable($targetDate);

            /** @var PeriodicEntry[] $periodicEntries */
            $periodicEntries = $this->messageBus->dispatch(new FindPeriodicEntriesQuery());
        } catch (Throwable $throwable) {
            $symfonyStyle->error($throwable->getMessage());
            $this->logger->error('Periodic entry command: Unable to execute the job', [
                'exceptionMessage' => $throwable->getMessage(),
                'exceptionClass'   => $throwable::class,
            ]);

            return Command::FAILURE;
        }

        // Only keep ids: a failing entry may reset the entity manager and detach loaded entities
        $periodicEntryIds = array_map(static fn (PeriodicEntry $periodicEntry): ?int => $periodicEntry->getId(), $periodicEntries);
        $failures         = 0;

        foreach ($periodicEntryIds as $periodicEntryId) {
            try {
                $this->messageBus->dispatch(new AddSplitForBudgetsCommand($periodicEntryId, $date));
            } catch (HandlerFailedException $exception) {
                if ($exception->getPrevious() instanceof PeriodicEntrySplitBudgetException) {
                    $symfonyStyle->info($exception->getMessage());
                    $this->logger->info($exception->getMessage(), [
                        '$periodicEntry' => $periodicEntryId,
                    ]);

                    continue;
                }

                $this->handleFailure($symfonyStyle, $exception, $periodicEntryId);
                ++$failures;
            } catch (Throwable $throwable) {
                $this->handleFailure($symfonyStyle, $throwable, $periodicEntryId);
                ++$failures;
            }
        }

        if ($failures > 0) {
            $symfonyStyle->warning(sprintf('The job has been executed with %d failure(s).', $failures));

            return Command::FAILURE;
        }

        $symfonyStyle->success('The job has been executed successfully.');

        return Command::SUCCESS;
    }

    private function handleFailure(SymfonyStyle $symfonyStyle, Throwable $throwable, ?int $periodicEntryId): void
    {
        $symfonyStyle->error(sprintf('Periodic entry #%s: %s', $periodicEntryId, $throwable->getMessage()));
        $this->logger->error('Periodic entry command: Unable to apply a periodic entry', [
            'periodicEntryId'  => $periodicEntryId,
            'exceptionMessage' => $throwable->getMessage(),
            'exceptionClass'   => $throwable::class,
        ]);

        // A failed flush closes the entity manager: reset it so the next periodic entries can still be applied
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->managerRegistry->getManager();
        if (!$entityManager->isOpen()) {
            $this->managerRegistry->resetManager();
        }
    }
}
