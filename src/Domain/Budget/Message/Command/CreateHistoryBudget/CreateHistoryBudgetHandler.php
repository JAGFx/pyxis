<?php

namespace App\Domain\Budget\Message\Command\CreateHistoryBudget;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateHistoryBudgetCommand
 */
readonly class CreateHistoryBudgetHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function __invoke(CreateHistoryBudgetCommand $command): void
    {
        /** @var HistoryBudget $historyBudget */
        $historyBudget = $this->objectMapper->map($command, HistoryBudget::class);

        $this->entityManager->persist($historyBudget);

        $this->entityManager->flush();
    }
}
