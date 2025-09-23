<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Command\CreateOrUpdateAccountCommand;
use App\Domain\Account\Message\Command\ToggleEnableAccountCommand;
use App\Domain\Account\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class AccountManager
{
    public function __construct(
        private AccountRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function toggle(ToggleEnableAccountCommand $command): void
    {
        $entity = $this->entityManager
            ->getRepository(Account::class)
            ->find($command->getOriginId());

        if (!$entity instanceof Account) {
            throw new LogicException('Account not found with id ' . $command->getOriginId());
        }

        $entity->setEnabled(!$entity->isEnabled());

        $this->entityManager->flush();
    }

    public function create(CreateOrUpdateAccountCommand $command, bool $flush = true): void
    {
        /** @var Account $account */
        $account = $this->objectMapper->map($command, Account::class);

        $this->repository->create($account);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(CreateOrUpdateAccountCommand $command, bool $flush = true): void
    {
        $entity = $this->entityManager
            ->getRepository(Account::class)
            ->find($command->getOriginId());

        if (!$entity instanceof Account) {
            throw new LogicException('Account not found with id ' . $command->getOriginId());
        }

        $this->objectMapper->map($command, $entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
