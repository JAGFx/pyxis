<?php

namespace App\Tests\Integration\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateOrUpdateEntryHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;

    private ObjectMapperInterface $objectMapper;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container           = static::getContainer();
        $this->messageBus    = $container->get(MessageBus::class);
        $this->objectMapper  = $container->get(ObjectMapperInterface::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create()->_real();

        $command = new CreateOrUpdateEntryCommand();
        $command->setName('Test Entry');
        $command->setAccount($account);
        $command->setAmount(150.0);
        $command->setBudget($budget);

        $this->messageBus->dispatch($command);

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

        $command = new CreateOrUpdateEntryCommand();
        $command->setName('Updated Entry');
        $command->setAccount($account);
        $command->setAmount(250.0);
        $command->setBudget($budget);
        $command->setOriginId($existingEntry->getId());

        $this->messageBus->dispatch($command);

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

        $this->objectMapper->map($entry, CreateOrUpdateEntryCommand::class);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateEntryWithAssignmentUpdatesAssignmentAmount(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create([
            'account' => $account,
            'amount'  => 1000.0, // Initial assignment amount
        ])->_real();

        $initialAssignmentAmount = $assignment->getAmount();
        $entryAmount             = -150.0; // Negative amount (expense)

        $command = new CreateOrUpdateEntryCommand();
        $command->setName('Test Entry with Assignment');
        $command->setAccount($account);
        $command->setAmount($entryAmount);
        $command->setAssignment($assignment);

        $this->messageBus->dispatch($command);

        // Refresh the assignment from database to get updated amount
        $this->entityManager->refresh($assignment);

        $expectedAmount = $initialAssignmentAmount + $entryAmount; // 1000.0 + (-150.0) = 850.0

        self::assertSame(
            $expectedAmount,
            $assignment->getAmount(),
            'Assignment amount should be updated by adding the entry amount'
        );
    }
}
