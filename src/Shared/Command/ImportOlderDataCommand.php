<?php

namespace App\Shared\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryFlagEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('bugr:old-data:import')]
class ImportOlderDataCommand
{
    public const string DEFAULT_ACCOUNT              = 'Livret A';

    public const string DEFAULT_BUDGET               = 'Budget par défaut';

    public const string DEFAULT_BUDGET_NAME_FORECAST = 'Import des provisions';

    private Connection $connection;

    private Connection $oldBugrManager;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManagerRegistry $managerRegistry,
    ) {
        $this->connection = $this->entityManager->getConnection();

        /** @var Connection $oldBugrManager */
        $oldBugrManager       = $this->managerRegistry->getConnection('old_bugr');
        $this->oldBugrManager = $oldBugrManager;
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(shortcut: '-a')] ?string $account = null,
        #[Option(shortcut: '-s')] ?float $amountSpent = null,
        #[Option(shortcut: '-f')] ?float $amountForecast = null,
    ): int {
        if (is_null($account)) {
            $this->createNewThings();

            $this->migrateBudget();
            $this->migratePeriodicEntry();
            $this->migratePeriodicEntriesBudget();

            return Command::SUCCESS;
        }

        $account = $this->entityManager
            ->getRepository(Account::class)
            ->findOneBy(['name' => $account]);

        if (is_null($account)) {
            $output->writeln('<error>Account not found</error>');

            return Command::FAILURE;
        }

        if (!is_null($amountSpent) && $amountSpent > 0) {
            $this->connection->insert('entry', [
                'name'       => 'Import des dépenses',
                'amount'     => $amountSpent,
                'created_at' => new DateTimeImmutable(),
                'updated_at' => new DateTimeImmutable(),
                'account_id' => $account->getId(),
                'flags'      => '["' . EntryFlagEnum::HIDDEN->value . '"]',
            ], [
                'created_at' => Types::DATETIME_IMMUTABLE,
                'updated_at' => Types::DATETIME_IMMUTABLE,
            ]);
        }

        if (!is_null($amountForecast) && $amountForecast > 0) {
            $budget = $this->entityManager
                ->getRepository(Budget::class)
                ->findOneBy(['name' => self::DEFAULT_BUDGET]);

            if (is_null($budget)) {
                $output->writeln('<error>Budget not found</error>');

                return Command::FAILURE;
            }

            $this->connection->insert('entry', [
                'name'       => self::DEFAULT_BUDGET_NAME_FORECAST,
                'amount'     => $amountForecast,
                'created_at' => new DateTimeImmutable(),
                'updated_at' => new DateTimeImmutable(),
                'account_id' => $account->getId(),
                'budget_id'  => $budget->getId(),
                'flags'      => '["' . EntryFlagEnum::HIDDEN->value . '"]',
            ], [
                'created_at' => Types::DATETIME_IMMUTABLE,
                'updated_at' => Types::DATETIME_IMMUTABLE,
            ]);
        }

        return Command::SUCCESS;
    }

    public function createNewThings(): void
    {
        $accounts = [self::DEFAULT_ACCOUNT, 'LDDS'];

        foreach ($accounts as $accountName) {
            $account = new Account()
                ->setName($accountName)
                ->setEnabled(true);

            $this->entityManager->persist($account);
        }

        $this->entityManager->flush();
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws NonUniqueResultException
     */
    private function migrateBudget(): void
    {
        $defaultAccount = $this->fetchDefaultAccount();

        if (is_null($defaultAccount)) {
            return;
        }

        $result = $this->oldBugrManager->executeQuery('SELECT b.* FROM budget b;');

        foreach ($result->fetchAllAssociative() as $oldBudget) {
            $budget = new Budget()
                ->setName($oldBudget['name'])
                ->setAmount($oldBudget['amount'])
                ->setEnabled($oldBudget['enable'])
            ;

            $budget->setCreatedAt(new DateTimeImmutable($oldBudget['created_at'] ?? ''));
            $budget->setUpdatedAt(new DateTimeImmutable($oldBudget['updated_at'] ?? ''));

            $this->entityManager->persist($budget);
        }

        $budgetDefault = new Budget()
            ->setName(self::DEFAULT_BUDGET)
            ->setAmount(0)
            ->setEnabled(false)
            ->setReadOnly(true)
        ;

        $this->entityManager->persist($budgetDefault);
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     * @throws NonUniqueResultException
     */
    private function migratePeriodicEntry(): void
    {
        $defaultAccount = $this->fetchDefaultAccount();

        if (is_null($defaultAccount)) {
            return;
        }

        $result = $this->oldBugrManager->executeQuery('SELECT pe.* FROM periodic_entry pe;');

        foreach ($result->fetchAllAssociative() as $oldPeriodicEntry) {
            $this->connection->insert('periodic_entry', [
                'name'           => $oldPeriodicEntry['name'],
                'amount'         => $oldPeriodicEntry['amount'],
                'execution_date' => $oldPeriodicEntry['execution_date'],
                'created_at'     => $oldPeriodicEntry['created_at'],
                'updated_at'     => $oldPeriodicEntry['updated_at'],
                'account_id'     => $defaultAccount->getId(),
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function migratePeriodicEntriesBudget(): void
    {
        $result = $this->oldBugrManager->executeQuery('
            SELECT peb.*, pe.name as periodic_entry_name, b.name as budget_name
            FROM periodic_entry_budget peb
            JOIN periodic_entry pe ON pe.id = peb.periodic_entry_id
            JOIN budget b ON peb.budget_id = b.id
        ');

        foreach ($result->fetchAllAssociative() as $oldPeriodicEntryBudget) {
            $periodicEntry = $this->connection->fetchAssociative('SELECT pe.* FROM periodic_entry pe WHERE pe.name = :name', [
                'name' => $oldPeriodicEntryBudget['periodic_entry_name'],
            ]);
            $budget = $this->connection->fetchAssociative('SELECT b.* FROM budget b WHERE b.name = :name', [
                'name' => $oldPeriodicEntryBudget['budget_name'],
            ]);

            if (false === $periodicEntry || false === $budget) {
                continue;
            }

            $this->connection->insert('periodic_entry_budget', [
                'periodic_entry_id' => $periodicEntry['id'],
                'budget_id'         => $budget['id'],
            ]);
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function fetchDefaultAccount(): ?Account
    {
        /** @var Account $account */
        $account = $this->entityManager
            ->createQueryBuilder()
            ->from(Account::class, 'a')
            ->select('a')
            ->andWhere('a.name = :name')
            ->setParameter('name', self::DEFAULT_ACCOUNT)
            ->getQuery()
            ->getOneOrNullResult();

        return $account;
    }
}
