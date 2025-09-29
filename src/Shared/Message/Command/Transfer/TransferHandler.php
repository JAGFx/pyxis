<?php

namespace App\Shared\Message\Command\Transfer;

use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see TransferCommand
 */
readonly class TransferHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function __invoke(TransferCommand $transfer): void
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
