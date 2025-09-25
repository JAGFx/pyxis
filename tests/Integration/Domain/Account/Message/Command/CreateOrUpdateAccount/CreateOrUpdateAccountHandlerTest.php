<?php

namespace App\Tests\Integration\Domain\Account\Message\Command\CreateOrUpdateAccount;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Command\CreateOrUpdateAccount\CreateOrUpdateAccountCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateOrUpdateAccountHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->messageBus   = $container->get(MessageBus::class);
        $this->objectMapper = $container->get(ObjectMapperInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        $command = new CreateOrUpdateAccountCommand();
        $command->setName('Test Account');

        $this->messageBus->dispatch($command);

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
        $command->setOriginId($existingAccount->getId());

        $this->messageBus->dispatch($command);

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
