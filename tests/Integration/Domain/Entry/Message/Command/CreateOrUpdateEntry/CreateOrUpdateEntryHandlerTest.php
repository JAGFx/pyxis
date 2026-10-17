<?php

namespace App\Tests\Integration\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Message\Command\RemoveEntry\RemoveEntryCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
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

    public function testCreateEntryWithAssignmentLinksItAndLowersTheBalance(): void
    {
        $assignment = $this->createAssignmentWithAmount(1000.0);

        $this->dispatchEntry($assignment, -150.0);

        $assignment = $this->reloadAssignment($assignment);
        self::assertSame(1000.0, $assignment->getAmount(), 'The starting amount is never written by entries');
        self::assertSame(850.0, $assignment->getBalance());
        self::assertCount(1, $assignment->getEntries());
    }

    public function testRemovingALinkedEntryRestoresTheBalance(): void
    {
        $assignment = $this->createAssignmentWithAmount(1000.0);
        $entryId    = $this->dispatchEntry($assignment, -150.0);

        $this->messageBus->dispatch(new RemoveEntryCommand()->setOriginId($entryId));

        self::assertSame(1000.0, $this->reloadAssignment($assignment)->getBalance());
    }

    public function testUpdatingALinkedEntryAdjustsTheBalance(): void
    {
        $assignment = $this->createAssignmentWithAmount(1000.0);
        $entryId    = $this->dispatchEntry($assignment, -150.0);

        /** @var Entry $entry */
        $entry   = $this->entityManager->find(Entry::class, $entryId);
        $command = $this->objectMapper->map($entry, CreateOrUpdateEntryCommand::class);
        $command->setOriginId($entryId)->setAmount(-400.0);
        $this->messageBus->dispatch($command);

        $assignment = $this->reloadAssignment($assignment);
        self::assertSame(600.0, $assignment->getBalance());
        self::assertCount(1, $assignment->getEntries(), 'The entry stays linked on update');
    }

    public function testUpdatingALinkedEntryBeyondTheBalanceIsRejected(): void
    {
        $assignment = $this->createAssignmentWithAmount(1000.0);
        $entryId    = $this->dispatchEntry($assignment, -150.0);

        /** @var Entry $entry */
        $entry   = $this->entityManager->find(Entry::class, $entryId);
        $command = $this->objectMapper->map($entry, CreateOrUpdateEntryCommand::class);
        $command->setOriginId($entryId)->setAmount(-1001.0);

        $this->expectException(ValidationFailedException::class);

        $this->messageBus->dispatch($command);
    }

    public function testEntryWithBudgetAndAssignmentIsRejected(): void
    {
        $assignment = $this->createAssignmentWithAmount(1000.0);
        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create()->_real();

        $command = new CreateOrUpdateEntryCommand(
            account: $assignment->getAccount(),
            name: 'Forecast entry with assignment',
            amount: -100.0,
            budget: $budget,
            assignment: $assignment,
        );

        try {
            $this->messageBus->dispatch($command);
            self::fail('A ValidationFailedException was expected');
        } catch (ValidationFailedException $exception) {
            self::assertCount(1, $exception->getViolations());
            self::assertSame('assignment', $exception->getViolations()->get(0)->getPropertyPath());
        }
    }

    private function createAssignmentWithAmount(float $amount): Assignment
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'amount' => $amount])->_real();

        return $assignment;
    }

    private function dispatchEntry(Assignment $assignment, float $amount): int
    {
        $this->messageBus->dispatch(new CreateOrUpdateEntryCommand(
            account: $assignment->getAccount(),
            name: 'Entry linked to the assignment',
            amount: $amount,
            assignment: $assignment,
        ));

        /** @var Entry $entry */
        $entry = EntryFactory::repository()->findOneBy(['name' => 'Entry linked to the assignment']);

        return (int) $entry->getId();
    }

    private function reloadAssignment(Assignment $assignment): Assignment
    {
        $id = $assignment->getId();
        $this->entityManager->clear();

        /** @var Assignment $reloaded */
        $reloaded = $this->entityManager->find(Assignment::class, $id);

        return $reloaded;
    }
}
