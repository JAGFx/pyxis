<?php

namespace App\Shared\Message\Command\AddSplitForBudgets;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see AddSplitForBudgetsCommand
 */
readonly class AddSplitForBudgetsHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBus $messageBus,
        private EntityFinder $entityFinder,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     * @throws ReflectionException
     * @throws PeriodicEntrySplitBudgetException
     * @throws ExceptionInterface
     */
    public function __invoke(AddSplitForBudgetsCommand $command): void
    {
        $periodicEntry = $this->entityFinder->findByIntIdentifierOrFail(
            PeriodicEntry::class,
            $command->getPeriodicEntryId()
        );

        if (!$periodicEntry->isEnabled()) {
            throw new PeriodicEntrySplitBudgetException('The periodic entry is disabled.');
        }

        // Use the date from command or current date if not provided
        $currentDate = $command->getDate() ?? new DateTimeImmutable();

        if ($periodicEntry->getExecutionDate()->format('j') !== $currentDate->format('j')) {
            throw new PeriodicEntrySplitBudgetException('The periodic entry is not scheduled for today.');
        }

        $date                    = $currentDate;
        $firstDateOfCurrentMonth = $date->modify('first day of this month 00:00:00');
        $lastDateOfCurrentMonth  = $date->modify('last day of this month 23:59:59');

        if (!is_null($periodicEntry->getLastExecutionDate())
            && $periodicEntry->getLastExecutionDate() >= $firstDateOfCurrentMonth
            && $periodicEntry->getLastExecutionDate() <= $lastDateOfCurrentMonth
        ) {
            throw new PeriodicEntrySplitBudgetException('A periodic entry has already been executed.');
        }

        // Re-enabled while the target is still reached: disable it again without feeding the assignment.
        // No exception here, it would roll back the transaction and the disabling with it.
        if ($periodicEntry->hasReachedAssignmentTarget()) {
            $this->disableOnTargetReached($periodicEntry, false);
            $this->entityManager->flush();

            return;
        }

        if ($periodicEntry->isSpent()) {
            $entryCommand = new CreateOrUpdateEntryCommand(
                account: $periodicEntry->getAccount(),
                name: $periodicEntry->getName(),
                amount: $periodicEntry->getAmount() ?? 0,
                flags: [EntryFlagEnum::PERIODIC_ENTRY],
                assignment: $periodicEntry->getAssignment(),
            );

            $this->messageBus->dispatch($entryCommand);
        } else {
            foreach ($periodicEntry->getBudgets() as $budget) {
                $amount = $periodicEntry->getAmountFor($budget, (int) $currentDate->format('n'));

                if ($amount <= 0) {
                    continue;
                }

                $entryCommand = new CreateOrUpdateEntryCommand(
                    account: $periodicEntry->getAccount(),
                    name: mb_substr($periodicEntry->getName() . ' - ' . $budget->getName(), 0, Entry::NAME_MAX_LENGTH),
                    amount: $amount,
                    budget: $budget,
                    flags: [EntryFlagEnum::PERIODIC_ENTRY],
                );

                $this->messageBus->dispatch($entryCommand);
            }
        }

        $periodicEntry->setLastExecutionDate($currentDate);

        // The entry created above is already in the assignment balance
        if ($periodicEntry->hasReachedAssignmentTarget()) {
            $this->disableOnTargetReached($periodicEntry, true);
        }

        $this->entityManager->flush();
    }

    private function disableOnTargetReached(PeriodicEntry $periodicEntry, bool $applied): void
    {
        $periodicEntry->setEnabled(false);
        $this->logger->info('Periodic entry disabled: assignment target reached', [
            'periodicEntryId' => $periodicEntry->getId(),
            'assignmentId'    => $periodicEntry->getAssignment()?->getId(),
            'applied'         => $applied,
        ]);
    }
}
