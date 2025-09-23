<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Command\CreateOrUpdateAccountCommand;
use App\Domain\Account\Message\Command\ToggleEnableAccountCommand;
use App\Domain\Account\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $account = $command->getAccount();
        $account->setEnabled(!$account->isEnabled());

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
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
