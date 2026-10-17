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
            'amount'  => 20000,
        ]);

        $command = new CreateOrUpdateAssignmentCommand();
        $command->setName('Test Assignment');
        $command->setAccount($account);
        $command->setAmount(15000);

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
            'amount'  => 30000,
        ]);

        $command = new CreateOrUpdateAssignmentCommand();
        $command->setName('Updated Assignment');
        $command->setAccount($account);
        $command->setAmount(25000);
        $command->setOriginId($existingAssignment->getId());

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateMustFailWhenOtherAssignmentsAlreadyHoldTheAccountTotal(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 100000]);
        AssignmentFactory::new()->create(['account' => $account, 'amount' => 80000]);

        $command = new CreateOrUpdateAssignmentCommand('Second assignment', 30000, $account);

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
        EntryFactory::new()->create(['account' => $account, 'amount' => 100000]);
        AssignmentFactory::new()->create(['account' => $account, 'amount' => 80000]);

        $this->messageBus->dispatch(new CreateOrUpdateAssignmentCommand('Second assignment', 20000, $account));

        self::assertCount(2, AssignmentFactory::repository()->findBy(['account' => $account]));
    }

    public function testUpdateDoesNotCountTheUpdatedAssignmentTwice(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 100000]);
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'amount' => 80000])->_real();

        $command = new CreateOrUpdateAssignmentCommand('Raised assignment', 100000, $account);
        $command->setOriginId($assignment->getId());

        $this->messageBus->dispatch($command);

        self::assertSame(100000, $assignment->getAmount());
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

    public function testEnvelopeWithoutStartingAmountAndWithTargetIsAccepted(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        $this->messageBus->dispatch(new CreateOrUpdateAssignmentCommand('Vacances', 0, $account, 200000));

        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::repository()->findOneBy(['name' => 'Vacances'])->_real();
        self::assertSame(0, $assignment->getBalance());
        self::assertSame(200000, $assignment->getTarget());
    }

    public function testUpdatingTheStartingAmountTakesLinkedEntriesIntoAccount(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 100000]);
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'amount' => 80000])->_real();
        $linked     = EntryFactory::new()->create(['account' => $account, 'amount' => -30000, 'assignment' => $assignment])->_real();
        $assignment->getEntries()->add($linked);

        // Account total 700, linked entries -300: a starting amount of 1000 gives a balance of 700, the maximum
        $command = new CreateOrUpdateAssignmentCommand('Raised', 100000, $account);
        $command->setOriginId($assignment->getId());
        $this->messageBus->dispatch($command);
        self::assertSame(70000, $assignment->getBalance());

        $command = new CreateOrUpdateAssignmentCommand('Too high', 100001, $account);
        $command->setOriginId($assignment->getId());

        try {
            $this->messageBus->dispatch($command);
            self::fail('A ValidationFailedException was expected');
        } catch (ValidationFailedException $exception) {
            self::assertStringContainsString('1 000,00', (string) $exception->getViolations()->get(0)->getMessage());
        }
    }

    public function testAccountCannotChangeWhenEntriesAreLinked(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        /** @var Account $otherAccount */
        $otherAccount = AccountFactory::new()->create()->_real();
        EntryFactory::new()->create(['account' => $otherAccount, 'amount' => 500000]);
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'amount' => 10000])->_real();
        $linked     = EntryFactory::new()->create(['account' => $account, 'amount' => -5000, 'assignment' => $assignment])->_real();
        $assignment->getEntries()->add($linked);

        $command = new CreateOrUpdateAssignmentCommand('Moved', 10000, $otherAccount);
        $command->setOriginId($assignment->getId());

        try {
            $this->messageBus->dispatch($command);
            self::fail('A ValidationFailedException was expected');
        } catch (ValidationFailedException $exception) {
            self::assertSame('account', $exception->getViolations()->get(0)->getPropertyPath());
        }
    }
}
