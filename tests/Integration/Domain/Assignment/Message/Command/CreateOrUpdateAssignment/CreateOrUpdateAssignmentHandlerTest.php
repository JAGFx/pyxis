<?php

namespace App\Tests\Integration\Domain\Assignment\Message\Command\CreateOrUpdateAssignment;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment\CreateOrUpdateAssignmentCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateOrUpdateAssignmentHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container          = static::getContainer();
        $this->messageBus   = $container->get(MessageBus::class);
        $this->objectMapper = $container->get(ObjectMapperInterface::class);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCreateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        EntryFactory::new()->create([
            'account' => $account,
            'amount'  => 200.0,
        ]);

        $command = new CreateOrUpdateAssignmentCommand();
        $command->setName('Test Assignment');
        $command->setAccountId($account);
        $command->setAmount(150.0);

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws ExceptionInterface
     */
    public function testUpdateDoesNotThrowException(): void
    {
        /** @var Assignment $existingAssignment */
        $existingAssignment = AssignmentFactory::new()->create([
            'name' => 'Original Assignment',
        ])->_real();

        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        EntryFactory::new()->create([
            'account' => $account,
            'amount'  => 300.0,
        ]);

        $command = new CreateOrUpdateAssignmentCommand();
        $command->setName('Updated Assignment');
        $command->setAccountId($account);
        $command->setAmount(250.0);
        $command->setOriginId($existingAssignment->getId());

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create([
            'name' => 'Test Assignment for Mapping',
        ])->_real();

        $this->objectMapper->map($assignment, CreateOrUpdateAssignmentCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
