<?php

namespace App\Domain\Budget\Message\Command\CreateOrUpdateBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateBudgetCommand
 */
readonly class CreateOrUpdateBudgetHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private BudgetRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(CreateOrUpdateBudgetCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Budget $budget */
            $budget = $this->objectMapper->map($command, Budget::class);
            $this->repository->create($budget);
        } else {
            $entity = $this->findEntityByIntIdentifierOrFail(
                Budget::class,
                $command->getOriginId(),
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
