<?php

namespace App\Domain\Account\Message\Command\CreateOrUpdateAccount;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Doctrine\Exception\EntityNotFoundException;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateAccountCommand
 */
readonly class CreateOrUpdateAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws EntityNotFoundException
     */
    public function __invoke(CreateOrUpdateAccountCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Account $account */
            $account = $this->objectMapper->map($command, Account::class);

            $this->entityManager->persist($account);
        } else {
            $entity = $this->entityFinder->findByIntIdentifierOrFail(
                Account::class,
                $command->getOriginId()
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
