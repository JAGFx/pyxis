<?php

namespace App\Tests\Integration\Domain\Entry\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Message\Command\EntryCreateOrUpdateCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class EntryManagerTest extends KernelTestCase
{
    private EntryManager $entryManager;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container          = static::getContainer();
        $this->entryManager = $container->get(EntryManager::class);
        $this->objectMapper = $container->get(ObjectMapperInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create()->_real();

        $command = new EntryCreateOrUpdateCommand();
        $command->setName('Test Entry');
        $command->setAccount($account);
        $command->setAmount(150.0);
        $command->setBudget($budget);

        $this->entryManager->create($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Entry $existingEntry */
        $existingEntry = EntryFactory::new()->create([
            'account' => $account,
            'name'    => 'Original Entry',
        ])->_real();

        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create()->_real();

        $command = new EntryCreateOrUpdateCommand();
        $command->setName('Updated Entry');
        $command->setAccount($account);
        $command->setAmount(250.0);
        $command->setBudget($budget);
        $command->setOrigin($existingEntry);

        $this->entryManager->update($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Entry $entry */
        $entry = EntryFactory::new()->create([
            'account' => $account,
            'name'    => 'Test Entry for Mapping',
        ])->_real();

        $this->objectMapper->map($entry, EntryCreateOrUpdateCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
