<?php

namespace App\Tests\Integration\Domain\Budget\Manager;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Message\Command\CreateOrUpdateBudgetCommand;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class BudgetManagerTest extends KernelTestCase
{
    private BudgetManager $budgetManager;
    private ObjectMapperInterface $objectMapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $container           = static::getContainer();
        $this->budgetManager = $container->get(BudgetManager::class);
        $this->objectMapper  = $container->get(ObjectMapperInterface::class);
    }

    public function testCreateDoesNotThrowException(): void
    {
        $command = new CreateOrUpdateBudgetCommand(
            name: 'Complete Budget Test',
            amount: 1200.0,
            enabled: true
        );

        $this->budgetManager->create($command);

        $this->expectNotToPerformAssertions();
    }

    public function testUpdateDoesNotThrowException(): void
    {
        /** @var Budget $existingBudget */
        $existingBudget = BudgetFactory::new()->create([
            'name'    => 'Original Budget',
            'amount'  => 800.0,
            'enabled' => true,
        ])->_real();

        $command = new CreateOrUpdateBudgetCommand(
            name: 'Updated Complete Budget',
            amount: 1500.0,
            enabled: false
        );
        $command->setOrigin($existingBudget);

        $this->budgetManager->update($command);

        $this->expectNotToPerformAssertions();
    }

    public function testObjectMapperMappingDoesNotThrowException(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create([
            'name'    => 'Test Budget for Mapping',
            'amount'  => 1000.0,
            'enabled' => true,
        ])->_real();

        $this->objectMapper->map($budget, CreateOrUpdateBudgetCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
