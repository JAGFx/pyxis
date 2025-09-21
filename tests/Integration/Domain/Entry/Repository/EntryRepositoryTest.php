<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Entry\Repository;

use App\Domain\Account\Entity\Account;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\Entry\Message\Query\FindEntriesQuery;
use App\Domain\Entry\Message\Query\GetEntryBalanceQuery;
use App\Domain\Entry\Repository\EntryRepository;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class EntryRepositoryTest extends KernelTestCase
{
    private const string ACCOUNT_1 = 'Account 1';
    private const string ACCOUNT_2 = 'Account 2';
    private const string BUDGET_1  = 'Budget 1';
    private const string BUDGET_2  = 'Budget 2';
    private EntryRepository $entryRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entryRepository = $container->get(EntryRepository::class);
    }

    private function populateDatabaseBalance(bool $withEntries = true): void
    {
        // Create accounts
        $account1 = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $account2 = AccountFactory::new()->create(['name' => self::ACCOUNT_2]);

        // Create budgets
        $budget1 = BudgetFactory::new()->create(['name' => self::BUDGET_1]);
        $budget2 = BudgetFactory::new()->create(['name' => self::BUDGET_2]);

        if (!$withEntries) {
            return;
        }

        // Create entries for account1 and budget1 (sum = 150)
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget1,
            'amount'  => 100.0,
            'name'    => 'Entry 1',
        ]);
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget1,
            'amount'  => 50.0,
            'name'    => 'Entry 2',
        ]);

        // Create entries for account1 and budget2 (sum = 75)
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget2,
            'amount'  => 75.0,
            'name'    => 'Entry 3',
        ]);

        // Create entries for account2 and budget1 (sum = 200)
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => $budget1,
            'amount'  => 200.0,
            'name'    => 'Entry 4',
        ]);

        // Create entries for account2 and budget2 (sum = -50)
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => $budget2,
            'amount'  => -50.0,
            'name'    => 'Entry 5',
        ]);

        // Create entries with no budget (should be grouped under NULL budget id)
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => null,
            'amount'  => 25.0,
            'name'    => 'Entry 6',
        ]);
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => null,
            'amount'  => 30.0,
            'name'    => 'Entry 7',
        ]);
    }

    public function testBalanceWithoutAccountFilter(): void
    {
        $this->populateDatabaseBalance();

        $query = new GetEntryBalanceQuery();

        $queryBuilder   = $this->entryRepository->balance($query);
        $entriesBalance = $queryBuilder->getQuery()->getResult();

        self::assertCount(3, $entriesBalance, 'Should return 3 groups (2 budgets + 1 null budget)');

        // Sort results by budget id for predictable assertions
        usort($entriesBalance, fn ($budgetBalance1, $budgetBalance2) => ($budgetBalance1['id'] ?? 0) <=> ($budgetBalance2['id'] ?? 0));

        // Check null budget group (entries without budget)
        self::assertNull($entriesBalance[0]['id'], 'First group should have null budget id');
        self::assertEquals(55.0, $entriesBalance[0]['sum'], 'Sum for entries without budget should be 55 (25 + 30)');

        // Check budget groups
        self::assertNotNull($entriesBalance[1]['id'], 'Second group should have a budget id');
        self::assertEquals(350.0, $entriesBalance[1]['sum'], 'Sum for first budget should be 350 (100 + 50 + 200)');

        self::assertNotNull($entriesBalance[2]['id'], 'Third group should have a budget id');
        self::assertEquals(25.0, $entriesBalance[2]['sum'], 'Sum for second budget should be 25 (75 + (-50))');
    }

    public function testBalanceWithAccountFilter(): void
    {
        $this->populateDatabaseBalance();

        $account1 = AccountFactory::repository()->findOneBy(['name' => self::ACCOUNT_1])->_real();

        $query = new GetEntryBalanceQuery(account: $account1);

        $queryBuilder   = $this->entryRepository->balance($query);
        $entriesBalance = $queryBuilder->getQuery()->getResult();

        self::assertCount(3, $entriesBalance, 'Should return 3 groups for account 1');

        // Sort results by budget id for predictable assertions
        usort($entriesBalance, fn ($budgetBalance1, $budgetBalance2) => ($budgetBalance1['id'] ?? 0) <=> ($budgetBalance2['id'] ?? 0));

        // Check null budget group (entries without budget for account1)
        self::assertNull($entriesBalance[0]['id'], 'First group should have null budget id');
        self::assertEquals(25.0, $entriesBalance[0]['sum'], 'Sum for account1 entries without budget should be 25');

        // Check budget groups for account1
        self::assertNotNull($entriesBalance[1]['id'], 'Second group should have a budget id');
        self::assertEquals(150.0, $entriesBalance[1]['sum'], 'Sum for account1 first budget should be 150 (100 + 50)');

        self::assertNotNull($entriesBalance[2]['id'], 'Third group should have a budget id');
        self::assertEquals(75.0, $entriesBalance[2]['sum'], 'Sum for account1 second budget should be 75');
    }

    public function testBalanceWithNonExistentAccount(): void
    {
        $this->populateDatabaseBalance();

        /** @var Account $nonExistentAccount */
        $nonExistentAccount = AccountFactory::new()->create(['name' => 'Non Existent'])->_real();

        $query = new GetEntryBalanceQuery(account: $nonExistentAccount);

        $queryBuilder   = $this->entryRepository->balance($query);
        $entriesBalance = $queryBuilder->getQuery()->getResult();

        self::assertEmpty($entriesBalance, 'Should return empty array for account with no entries');
    }

    public function testBalanceWithNoEntries(): void
    {
        $this->populateDatabaseBalance(false);

        $query = new GetEntryBalanceQuery();

        $queryBuilder   = $this->entryRepository->balance($query);
        $entriesBalance = $queryBuilder->getQuery()->getResult();

        self::assertEmpty($entriesBalance, 'Should return empty array when no entries exist');
    }

    private function populateDatabaseFiter(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $budget  = BudgetFactory::new()->create(['name' => self::BUDGET_1]);

        // Create entries WITH budget (TYPE_FORECAST)
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 100.0,
            'name'    => 'Forecast Entry 1',
        ]);
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 200.0,
            'name'    => 'Forecast Entry 2',
        ]);

        // Create entries WITHOUT budget (TYPE_SPENT)
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => null,
            'amount'  => 50.0,
            'name'    => 'Spent Entry 1',
        ]);
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => null,
            'amount'  => 75.0,
            'name'    => 'Spent Entry 2',
        ]);
    }

    public function testGetTypeFilterWithTypeSpent(): void
    {
        $this->populateDatabaseFiter();

        $searchQuery = new FindEntriesQuery(type: EntryTypeEnum::TYPE_SPENT);

        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(2, $entries, 'Should return only entries with null budget for TYPE_SPENT');

        foreach ($entries as $entry) {
            self::assertNull($entry->getBudget(), 'All entries should have null budget for TYPE_SPENT');
            self::assertStringContainsString('Spent', $entry->getName(), 'Entry name should contain "Spent"');
        }
    }

    public function testGetTypeFilterWithTypeForecast(): void
    {
        $this->populateDatabaseFiter();

        $searchQuery = new FindEntriesQuery(type: EntryTypeEnum::TYPE_FORECAST);

        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        $this->assertCount(2, $entries, 'Should return only entries with budget for TYPE_FORECAST');

        foreach ($entries as $entry) {
            $this->assertNotNull($entry->getBudget(), 'All entries should have a budget for TYPE_FORECAST');
            $this->assertStringContainsString('Forecast', $entry->getName(), 'Entry name should contain "Forecast"');
        }
    }

    public function testGetTypeFilterWithNullType(): void
    {
        $this->populateDatabaseFiter();

        $searchQuery = new FindEntriesQuery(type: null);

        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        $this->assertCount(4, $entries, 'Should return all entries when type is null');

        $entriesWithBudget    = array_filter($entries, fn ($entry) => null !== $entry->getBudget());
        $entriesWithoutBudget = array_filter($entries, fn ($entry) => null === $entry->getBudget());

        $this->assertCount(2, $entriesWithBudget, 'Should have 2 entries with budget');
        $this->assertCount(2, $entriesWithoutBudget, 'Should have 2 entries without budget');
    }

    private function populateDatabaseForFlagsUnion(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $budget  = BudgetFactory::new()->create(['name' => self::BUDGET_1]);

        // Entries with single flags
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 100.0,
            'name'    => 'Entry with only BALANCE',
            'flags'   => [EntryFlagEnum::BALANCE],
        ]);

        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 200.0,
            'name'    => 'Entry with only TRANSFERT',
            'flags'   => [EntryFlagEnum::TRANSFERT],
        ]);

        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 150.0,
            'name'    => 'Entry with only HIDDEN',
            'flags'   => [EntryFlagEnum::HIDDEN],
        ]);

        // Entries with exactly 2 flags
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 300.0,
            'name'    => 'Entry with BALANCE and TRANSFERT',
            'flags'   => [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT],
        ]);

        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 250.0,
            'name'    => 'Entry with BALANCE and HIDDEN',
            'flags'   => [EntryFlagEnum::BALANCE, EntryFlagEnum::HIDDEN],
        ]);

        // Entry with 3 flags
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 400.0,
            'name'    => 'Entry with BALANCE, TRANSFERT and HIDDEN',
            'flags'   => [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT, EntryFlagEnum::HIDDEN],
        ]);

        // Entry with all 4 flags
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 500.0,
            'name'    => 'Entry with all flags',
            'flags'   => [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT, EntryFlagEnum::HIDDEN, EntryFlagEnum::PERIODIC_ENTRY],
        ]);

        // Entry with empty flags
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 50.0,
            'name'    => 'Entry with empty flags',
            'flags'   => [],
        ]);
    }

    public function testFlagsUnionBehavior(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Test search with multiple flags - OR logic (union)
        $searchQuery  = new FindEntriesQuery(flags: [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT]);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);

        /** @var Entry[] $entries */
        $entries = $queryBuilder->getQuery()->getResult();

        // Should return all entries that have BALANCE OR TRANSFERT
        self::assertCount(6, $entries, 'Should return 6 entries with BALANCE OR TRANSFERT flags');

        $entryNames = array_map(fn ($entry) => $entry->getName(), $entries);
        self::assertContains('Entry with only BALANCE', $entryNames);
        self::assertContains('Entry with only TRANSFERT', $entryNames);
        self::assertContains('Entry with BALANCE and TRANSFERT', $entryNames);
        self::assertContains('Entry with BALANCE and HIDDEN', $entryNames);
        self::assertContains('Entry with BALANCE, TRANSFERT and HIDDEN', $entryNames);
        self::assertContains('Entry with all flags', $entryNames);
    }

    public function testFlagsUnionSingleFlag(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Search for a single flag - should return all entries that contain this flag
        $searchQuery  = new FindEntriesQuery(flags: [EntryFlagEnum::HIDDEN]);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(4, $entries, 'Should return 4 entries containing HIDDEN flag');

        $entryNames = array_map(fn ($entry) => $entry->getName(), $entries);
        self::assertContains('Entry with only HIDDEN', $entryNames);
        self::assertContains('Entry with BALANCE and HIDDEN', $entryNames);
        self::assertContains('Entry with BALANCE, TRANSFERT and HIDDEN', $entryNames);
        self::assertContains('Entry with all flags', $entryNames);
    }

    public function testFlagsUnionWithUnflagged(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Test case [-1]: only entries without flags
        $searchQuery  = new FindEntriesQuery(flags: [-1]);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(1, $entries, 'Should return 1 entry with empty flags');

        $entry = $entries[0];
        self::assertEquals('Entry with empty flags', $entry->getName());
        self::assertEmpty($entry->getFlags());
    }

    public function testFlagsUnionWithFlagsAndUnflagged(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Test case [A, B, -1]: entries with flags A or B OR without flags
        $searchQuery  = new FindEntriesQuery(flags: [EntryFlagEnum::BALANCE, EntryFlagEnum::PERIODIC_ENTRY, -1]);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(6, $entries, 'Should return 6 entries with BALANCE OR PERIODIC_ENTRY OR empty flags');

        $entryNames = array_map(fn ($entry) => $entry->getName(), $entries);
        self::assertContains('Entry with only BALANCE', $entryNames);
        self::assertContains('Entry with BALANCE and TRANSFERT', $entryNames);
        self::assertContains('Entry with BALANCE and HIDDEN', $entryNames);
        self::assertContains('Entry with BALANCE, TRANSFERT and HIDDEN', $entryNames);
        self::assertContains('Entry with all flags', $entryNames);
        self::assertContains('Entry with empty flags', $entryNames);
    }

    public function testFlagsUnionEmptyArray(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Test case []: all entries
        $searchQuery  = new FindEntriesQuery(flags: []);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(8, $entries, 'Should return all 8 entries when flags array is empty');
    }

    public function testFlagsUnionThreeFlags(): void
    {
        $this->populateDatabaseForFlagsUnion();

        // Test with 3 different flags - OR logic
        $searchQuery  = new FindEntriesQuery(flags: [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT, EntryFlagEnum::PERIODIC_ENTRY]);
        $queryBuilder = $this->entryRepository->getEntriesQueryBuilder($searchQuery);
        $entries      = $queryBuilder->getQuery()->getResult();

        self::assertCount(6, $entries, 'Should return 6 entries with any of the three flags');

        $entryNames = array_map(fn ($entry) => $entry->getName(), $entries);
        self::assertContains('Entry with only BALANCE', $entryNames);
        self::assertContains('Entry with only TRANSFERT', $entryNames);
        self::assertContains('Entry with BALANCE and TRANSFERT', $entryNames);
        self::assertContains('Entry with BALANCE and HIDDEN', $entryNames);
        self::assertContains('Entry with BALANCE, TRANSFERT and HIDDEN', $entryNames);
        self::assertContains('Entry with all flags', $entryNames);
    }
}
