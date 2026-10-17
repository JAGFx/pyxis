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
use Symfony\Component\Messenger\Exception\ValidationFailedException;
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
        $command->setAccount($account);
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
        $command->setAccount($account);
        $command->setAmount(250.0);
        $command->setOriginId($existingAssignment->getId());

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateMustFailWhenOtherAssignmentsAlreadyHoldTheAccountTotal(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 1000.0]);
        AssignmentFactory::new()->create(['account' => $account, 'amount' => 800.0]);

        $command = new CreateOrUpdateAssignmentCommand('Second assignment', 300.0, $account);

        try {
            $this->messageBus->dispatch($command);
            self::fail('A ValidationFailedException was expected');
        } catch (ValidationFailedException $exception) {
            self::assertCount(1, $exception->getViolations());
            self::assertSame('amount', $exception->getViolations()->get(0)->getPropertyPath());
            self::assertStringContainsString('200,00', (string) $exception->getViolations()->get(0)->getMessage());
        }
    }

    public function testCreateWithinTheAvailableAmountIsAccepted(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 1000.0]);
        AssignmentFactory::new()->create(['account' => $account, 'amount' => 800.0]);

        $this->messageBus->dispatch(new CreateOrUpdateAssignmentCommand('Second assignment', 200.0, $account));

        self::assertCount(2, AssignmentFactory::repository()->findBy(['account' => $account]));
    }

    public function testUpdateDoesNotCountTheUpdatedAssignmentTwice(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 1000.0]);
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'amount' => 800.0])->_real();

        $command = new CreateOrUpdateAssignmentCommand('Raised assignment', 1000.0, $account);
        $command->setOriginId($assignment->getId());

        $this->messageBus->dispatch($command);

        self::assertSame(1000.0, $assignment->getAmount());
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
