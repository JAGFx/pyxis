<?php

namespace App\Domain\Budget\Message\Command\CreateOrUpdateBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Security\BudgetVoter;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Security\AuthorizationChecker;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateBudgetCommand
 */
readonly class CreateOrUpdateBudgetHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
        private EntityFinder $entityFinder,
        private AuthorizationChecker $authorizationChecker,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function __invoke(CreateOrUpdateBudgetCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Budget $budget */
            $budget = $this->objectMapper->map($command, Budget::class);
            $this->entityManager->persist($budget);
        } else {
            $entity = $this->entityFinder->findByIntIdentifierOrFail(
                Budget::class,
                $command->getOriginId(),
            );

            $this->authorizationChecker->denyAccessUnlessGranted(
                BudgetVoter::MANAGE,
                $entity
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
