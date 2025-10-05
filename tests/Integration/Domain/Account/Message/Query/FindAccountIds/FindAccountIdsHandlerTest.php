<?php

namespace App\Tests\Integration\Domain\Account\Message\Query\FindAccountIds;

use App\Domain\Account\Message\Query\FindAccountIds\FindAccountIdsQuery;
use App\Domain\Account\ValueObject\AccountIds;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class FindAccountIdsHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container        = static::getContainer();
        $this->messageBus = $container->get(MessageBus::class);
    }

    public function testFindAccountIdsWithZeroAccounts(): void
    {
        // Arrange - No accounts created, database should be empty due to ResetDatabase trait

        // Act
        /** @var AccountIds $result */
        $result = $this->messageBus->dispatch(new FindAccountIdsQuery());

        // Assert
        $this->assertInstanceOf(AccountIds::class, $result);
        $this->assertEmpty($result->getIds());
        $this->assertCount(0, $result->getIds());
        $this->assertEquals(0, $result->getTotal());
    }

    public function testFindAccountIdsWithSomeAccounts(): void
    {
        // Arrange - Create several accounts
        $account1 = AccountFactory::new()->create(['name' => 'Account Alpha']);
        $account2 = AccountFactory::new()->create(['name' => 'Account Beta']);
        $account3 = AccountFactory::new()->create(['name' => 'Account Gamma']);
        $account4 = AccountFactory::new()->create(['name' => 'Account Delta']);
        $account5 = AccountFactory::new()->create(['name' => 'Account Epsilon']);

        // Act - Query without maxResults limit
        /** @var AccountIds $result */
        $result = $this->messageBus->dispatch(new FindAccountIdsQuery());

        // Assert
        $this->assertInstanceOf(AccountIds::class, $result);

        // Should return all 5 accounts
        $this->assertCount(5, $result->getIds());
        $this->assertEquals(5, $result->getTotal());

        // Verify we get integer IDs
        foreach ($result->getIds() as $id) {
            $this->assertIsInt($id);
            $this->assertGreaterThan(0, $id);
        }

        // Verify accounts are ordered by name (as per repository implementation)
        $ids = $result->getIds();
        $this->assertEquals($account1->_real()->getId(), $ids[0]); // Account Alpha
        $this->assertEquals($account2->_real()->getId(), $ids[1]); // Account Beta
        $this->assertEquals($account4->_real()->getId(), $ids[2]); // Account Delta
        $this->assertEquals($account5->_real()->getId(), $ids[3]); // Account Epsilon
        $this->assertEquals($account3->_real()->getId(), $ids[4]); // Account Gamma
    }

    public function testFindAccountIdsWithMaxResultsLimit(): void
    {
        // Arrange - Create more accounts than the limit
        AccountFactory::new()->create(['name' => 'Account 01']);
        AccountFactory::new()->create(['name' => 'Account 02']);
        AccountFactory::new()->create(['name' => 'Account 03']);
        AccountFactory::new()->create(['name' => 'Account 04']);
        AccountFactory::new()->create(['name' => 'Account 05']);

        // Act - Query with maxResults = 3
        /** @var AccountIds $result */
        $result = $this->messageBus->dispatch(new FindAccountIdsQuery(maxResults: 3));

        // Assert
        $this->assertInstanceOf(AccountIds::class, $result);

        // Should return only 3 accounts due to maxResults
        $this->assertCount(3, $result->getIds());

        // Total count should still be 5 (all accounts in database)
        $this->assertEquals(5, $result->getTotal());

        // Should indicate there are more accounts available (manual check)
        $this->assertTrue(count($result->getIds()) < $result->getTotal());

        // Verify we get integer IDs
        foreach ($result->getIds() as $id) {
            $this->assertIsInt($id);
            $this->assertGreaterThan(0, $id);
        }
    }

    public function testFindAccountIdsMaxResultsEqualToTotalAccounts(): void
    {
        // Arrange - Create exactly the number of accounts as the limit
        AccountFactory::new()->create(['name' => 'Account A']);
        AccountFactory::new()->create(['name' => 'Account B']);
        AccountFactory::new()->create(['name' => 'Account C']);

        // Act - Query with maxResults equal to total count
        /** @var AccountIds $result */
        $result = $this->messageBus->dispatch(new FindAccountIdsQuery(maxResults: 3));

        // Assert
        $this->assertInstanceOf(AccountIds::class, $result);

        // Should return all 3 accounts
        $this->assertCount(3, $result->getIds());
        $this->assertEquals(3, $result->getTotal());

        // Should indicate no more accounts available (manual check)
        $this->assertEquals(count($result->getIds()), $result->getTotal());
    }

    public function testFindAccountIdsMaxResultsGreaterThanTotalAccounts(): void
    {
        // Arrange - Create fewer accounts than the limit
        AccountFactory::new()->create(['name' => 'Account X']);
        AccountFactory::new()->create(['name' => 'Account Y']);

        // Act - Query with maxResults greater than total count
        /** @var AccountIds $result */
        $result = $this->messageBus->dispatch(new FindAccountIdsQuery(maxResults: 10));

        // Assert
        $this->assertInstanceOf(AccountIds::class, $result);

        // Should return only the 2 existing accounts
        $this->assertCount(2, $result->getIds());
        $this->assertEquals(2, $result->getTotal());

        // Should indicate no more accounts available (manual check)
        $this->assertEquals(count($result->getIds()), $result->getTotal());
    }
}
