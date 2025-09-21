<?php

namespace App\Tests\Integration\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Account\Message\Command\CreateOrUpdateAccountCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class AccountManagerTest extends KernelTestCase
{
    private AccountManager $accountManager;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container            = static::getContainer();
        $this->accountManager = $container->get(AccountManager::class);
        $this->objectMapper   = $container->get(ObjectMapperInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        $command = new CreateOrUpdateAccountCommand();
        $command->setName('Test Account');

        $this->accountManager->create($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        /** @var Account $existingAccount */
        $existingAccount = AccountFactory::new()->create([
            'name' => 'Original Account',
        ])->_real();

        $command = new CreateOrUpdateAccountCommand();
        $command->setName('Updated Account');
        $command->setOrigin($existingAccount);

        $this->accountManager->update($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create([
            'name' => 'Test Account for Mapping',
        ])->_real();

        $this->objectMapper->map($account, CreateOrUpdateAccountCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
