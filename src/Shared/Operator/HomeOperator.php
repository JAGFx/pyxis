<?php

namespace App\Shared\Operator;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryKindEnum;
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
            ->setKind(EntryKindEnum::BALANCING)
            ->setBudget($transfer->getBudgetSource())
            ->setAmount(-$transfer->getAmount())
            ->setAccount($transfer->getAccount())
            ->setName(sprintf('TransferRequest depuis %s', $entrySourceName));

        $entryTarget = new Entry()
            ->setKind(EntryKindEnum::BALANCING)
            ->setBudget($transfer->getBudgetTarget())
            ->setAmount($transfer->getAmount())
            ->setAccount($transfer->getAccount())
            ->setName(sprintf('TransferRequest vers %s', $entryTargetName));

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
