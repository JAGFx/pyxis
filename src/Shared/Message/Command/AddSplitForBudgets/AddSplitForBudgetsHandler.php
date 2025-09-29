<?php

namespace App\Shared\Message\Command\AddSplitForBudgets;

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

        if ($periodicEntry->getExecutionDate()->format('j') !== new DateTimeImmutable()->format('j')) {
            throw new PeriodicEntrySplitBudgetException('The periodic entry is not scheduled for today.');
        }

        $date                    = $command->getDate() ?? new DateTimeImmutable();
        $firstDateOfCurrentMonth = $date->modify('first day of this month 00:00:00');
        $lastDateOfCurrentMonth  = $date->modify('last day of this month 23:59:59');

        if (!is_null($periodicEntry->getLastExecutionDate())
            && $periodicEntry->getLastExecutionDate() >= $firstDateOfCurrentMonth
            && $periodicEntry->getLastExecutionDate() <= $lastDateOfCurrentMonth
        ) {
            throw new PeriodicEntrySplitBudgetException('A periodic entry has already been executed.');
        }

        if ($periodicEntry->isSpent()) {
            $entryCommand = new CreateOrUpdateEntryCommand(
                account: $periodicEntry->getAccount(),
                name: $periodicEntry->getName(),
                amount: $periodicEntry->getAmount() ?? 0.0,
                flags: [EntryFlagEnum::PERIODIC_ENTRY],
            );

            $this->messageBus->dispatch($entryCommand);
        } else {
            foreach ($periodicEntry->getBudgets() as $budget) {
                $amount = $periodicEntry->getAmountFor($budget);

                if ($amount <= 0.0) {
                    continue;
                }

                $entryCommand = new CreateOrUpdateEntryCommand(
                    account: $periodicEntry->getAccount(),
                    name: $periodicEntry->getName() . ' - ' . $budget->getName(),
                    amount: $amount,
                    budget: $budget,
                    flags: [EntryFlagEnum::PERIODIC_ENTRY],
                );

                $this->messageBus->dispatch($entryCommand);
            }
        }

        $periodicEntry->setLastExecutionDate(new DateTimeImmutable());
        $this->entityManager->flush();
    }
}
