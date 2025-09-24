<?php

namespace App\Shared\Operator;

use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Cqs\Bus\SymfonyMessageBus;
use App\Shared\Request\TransferRequest;
use Doctrine\ORM\EntityManagerInterface;

readonly class HomeOperator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SymfonyMessageBus $messageBus,
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

        $this->messageBus->dispatch($entrySourceCommand);
        $this->messageBus->dispatch($entryTargetCommand);

        $this->entityManager->flush();
    }
}
