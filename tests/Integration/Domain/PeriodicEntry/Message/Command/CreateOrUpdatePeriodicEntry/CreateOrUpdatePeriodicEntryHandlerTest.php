<?php

namespace App\Tests\Integration\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry\CreateOrUpdatePeriodicEntryCommand;
use App\Shared\Cqs\Bus\MessageBus;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateOrUpdatePeriodicEntryHandlerTest extends KernelTestCase
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

    public function testCreateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Test Account'])->_real();
        /** @var Budget $budget1 */
        $budget1 = BudgetFactory::new()->create(['name' => 'Test Budget 1', 'amount' => 1200.0])->_real();
        /** @var Budget $budget2 */
        $budget2 = BudgetFactory::new()->create(['name' => 'Test Budget 2', 'amount' => 600.0])->_real();

        $command = new CreateOrUpdatePeriodicEntryCommand(
            account: $account,
            name: 'Complete Periodic Entry Test',
            amount: null,
            executionDate: new DateTimeImmutable(),
            budgets: new ArrayCollection([$budget1, $budget2])
        );

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Original Account'])->_real();

        /** @var PeriodicEntry $existingPeriodicEntry */
        $existingPeriodicEntry = PeriodicEntryFactory::new()->create([
            'name'    => 'Original Periodic Entry',
            'account' => $account,
            'amount'  => 100.0,
        ])->_real();

        /** @var Account $newAccount */
        $newAccount = AccountFactory::new()->create(['name' => 'Updated Account'])->_real();
        /** @var Budget $budget1 */
        $budget1 = BudgetFactory::new()->create(['name' => 'Updated Budget 1', 'amount' => 800.0])->_real();
        /** @var Budget $budget2 */
        $budget2 = BudgetFactory::new()->create(['name' => 'Updated Budget 2', 'amount' => 400.0])->_real();

        $command = new CreateOrUpdatePeriodicEntryCommand(
            account: $newAccount,
            name: 'Updated Complete Periodic Entry',
            amount: null,
            executionDate: new DateTimeImmutable('+1 month'),
            budgets: new ArrayCollection([$budget1, $budget2]),
        );

        $command->setOriginId($existingPeriodicEntry->getId());

        $this->messageBus->dispatch($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Test Account'])->_real();

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'account' => $account,
            'name'    => 'Test Periodic Entry for Mapping',
        ])->_real();

        $this->objectMapper->map($periodicEntry, CreateOrUpdatePeriodicEntryCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
