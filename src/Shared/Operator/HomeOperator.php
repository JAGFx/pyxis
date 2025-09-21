<?php

namespace App\Shared\Operator;

use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntryCommand;
use App\Shared\Request\TransferRequest;
use Doctrine\ORM\EntityManagerInterface;

readonly class HomeOperator
{
    public function __construct(
        private EntryManager $entryManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function transfer(TransferRequest $transfer): void
    {
        $entrySourceName = $transfer->getBudgetSource()?->getName() ?? 'Dépense';
        $entryTargetName = $transfer->getBudgetTarget()?->getName() ?? 'Dépense';

        $entrySourceCommand = new CreateOrUpdateEntryCommand(
            account: $transfer->getAccount(),
            name: $entrySourceName,
            amount: -$transfer->getAmount(),
            budget: $transfer->getBudgetSource(),
            flags: [EntryFlagEnum::TRANSFERT],
        );

        $entryTargetCommand = new CreateOrUpdateEntryCommand(
            account: $transfer->getAccount(),
            name: $entryTargetName,
            amount: $transfer->getAmount(),
            budget: $transfer->getBudgetTarget(),
            flags: [EntryFlagEnum::TRANSFERT],
        );

        $this->entryManager->create($entrySourceCommand, false);
        $this->entryManager->create($entryTargetCommand, false);

        $this->entityManager->flush();
    }
}
