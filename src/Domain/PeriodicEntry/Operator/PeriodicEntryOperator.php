<?php

namespace App\Domain\PeriodicEntry\Operator;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Model\EntryKindEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use DateMalformedStringException;
use DateTimeImmutable;

class PeriodicEntryOperator
{
    public function __construct(
        private readonly EntryManager $entryManager,
        private readonly PeriodicEntryManager $periodicEntryManager,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws PeriodicEntrySplitBudgetException
     */
    public function addSplitForBudgets(PeriodicEntry $periodicEntry, ?DateTimeImmutable $date = null): void
    {
        $date ??= new DateTimeImmutable();
        $firstDateOfCurrentMonth = $date->modify('first day of this month 00:00:00');
        $lastDateOfCurrentMonth  = $date->modify('last day of this month 23:59:59');

        if (!is_null($periodicEntry->getLastExecutionDate())
            && $periodicEntry->getLastExecutionDate() >= $firstDateOfCurrentMonth
            && $periodicEntry->getLastExecutionDate() <= $lastDateOfCurrentMonth
        ) {
            throw new PeriodicEntrySplitBudgetException('A periodic entry has already been executed.');
        }

        if ($periodicEntry->isSpent()) {
            $entry = new Entry()
                ->setAmount($periodicEntry->getAmount() ?? 0.0)
                ->setKind(EntryKindEnum::BALANCING)
                ->setName($periodicEntry->getName())
                ->setAccount($periodicEntry->getAccount())
            ;
            $this->entryManager->create($entry);
        } else {
            foreach ($periodicEntry->getBudgets() as $budget) {
                $amount = $periodicEntry->getAmountFor($budget);

                if ($amount <= 0.0) {
                    continue;
                }

                $entry = new Entry()
                    ->setAmount($amount)
                    ->setBudget($budget)
                    ->setKind(EntryKindEnum::BALANCING)
                    ->setName($periodicEntry->getName() . ' - ' . $budget->getName())
                    ->setAccount($periodicEntry->getAccount())
                ;
                $this->entryManager->create($entry);
            }
        }

        $periodicEntry->setLastExecutionDate(new DateTimeImmutable());
        $this->periodicEntryManager->update($periodicEntry);
    }
}
