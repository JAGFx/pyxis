<?php

namespace App\Domain\Account\Message\Command\CreateOrUpdateAccount;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateAccountCommand
 */
readonly class CreateOrUpdateAccountHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private AccountRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(CreateOrUpdateAccountCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Account $account */
            $account = $this->objectMapper->map($command, Account::class);

            $this->repository->create($account);
        } else {
            $entity = $this->findEntityByIntIdentifierOrFail(
                Account::class,
                $command->getOriginId()
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
