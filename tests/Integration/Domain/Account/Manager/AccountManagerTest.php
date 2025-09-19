<?php

namespace App\Tests\Integration\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Account\Message\Command\AccountCreateOrUpdateCommand;
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
        // Given
        $command = new AccountCreateOrUpdateCommand();
        $command->setName('Test Account');

        // When & Then - Should not throw any exception
        $this->accountManager->create($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        // Given
        /** @var Account $existingAccount */
        $existingAccount = AccountFactory::new()->create([
            'name' => 'Original Account',
        ])->_real();

        $command = new AccountCreateOrUpdateCommand();
        $command->setName('Updated Account');
        $command->setOrigin($existingAccount);

        // When & Then - Should not throw any exception
        $this->accountManager->update($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        // Given - Création d'une entité Account
        /** @var Account $account */
        $account = AccountFactory::new()->create([
            'name' => 'Test Account for Mapping',
        ])->_real();

        // When & Then - ObjectMapper mapping should not throw any exception
        $this->objectMapper->map($account, AccountCreateOrUpdateCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
