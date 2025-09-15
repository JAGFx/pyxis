<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Account\Repository;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Repository\AccountRepository;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class AccountRepositoryTest extends KernelTestCase
{
    private AccountRepository $accountRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->accountRepository = $container->get(AccountRepository::class);
    }

    public function testHasPositiveOrNegativeBalance(): void
    {
        // Account with zero balance (entries sum to 0)
        $zeroAccount = AccountFactory::new(['name' => 'Zero Account', 'enabled' => true])->create();
        EntryFactory::new(['account' => $zeroAccount, 'amount' => 100])->create();
        EntryFactory::new(['account' => $zeroAccount, 'amount' => -100])->create();

        // Account with positive balance
        $positiveAccount = AccountFactory::new(['name' => 'Positive Account', 'enabled' => true])->create();
        EntryFactory::new(['account' => $positiveAccount, 'amount' => 150])->create();
        EntryFactory::new(['account' => $positiveAccount, 'amount' => 50])->create();

        // Account with negative balance
        $negativeAccount = AccountFactory::new(['name' => 'Negative Account', 'enabled' => true])->create();
        EntryFactory::new(['account' => $negativeAccount, 'amount' => -75])->create();

        // Account with no entries
        AccountFactory::new(['name' => 'Empty Account', 'enabled' => true])->create();

        $command = new AccountSearchCommand()->setPositiveOrNegativeBalance(true);

        $queryBuilder = $this->accountRepository->getAccountsQueryBuilder($command);
        $results      = $queryBuilder->getQuery()->getResult();

        $accountNames = array_map(fn ($account) => $account->getName(), $results);
        sort($accountNames);

        $this->assertCount(2, $results);
        $this->assertEquals(['Negative Account', 'Positive Account'], $accountNames);
    }
}
