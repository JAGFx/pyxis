<?php

namespace App\Shared\Operator;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Manager\EntryManager;
use App\Shared\Request\TransferRequest;

readonly class HomeOperator
{
    public function __construct(
        private EntryManager $entryManager,
    ) {
    }

    public function transfer(TransferRequest $transfer): void
    {
        $entrySourceName = $transfer->getBudgetSource()?->getName() ?? 'Dépense';
        $entryTargetName = $transfer->getBudgetTarget()?->getName() ?? 'Dépense';

        $entrySource = new Entry()
            ->setBudget($transfer->getBudgetSource())
            ->setAmount(-$transfer->getAmount())
            ->setAccount($transfer->getAccount())
            ->setName($entrySourceName)
            ->addFlag(EntryFlagEnum::TRANSFERT);

        $entryTarget = new Entry()
            ->setBudget($transfer->getBudgetTarget())
            ->setAmount($transfer->getAmount())
            ->setAccount($transfer->getAccount())
            ->setName($entryTargetName)
            ->addFlag(EntryFlagEnum::TRANSFERT);

        $transfer
            ->getBudgetSource()
            ?->addEntry($entrySource);

        $transfer
            ->getBudgetTarget()
            ?->addEntry($entryTarget);

        $this->entryManager->create($entrySource);
        $this->entryManager->create($entryTarget);
    }
}
