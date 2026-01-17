<?php

namespace App\Tests\Integration\Domain\Budget\Message\Command\CreateOrUpdateBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Command\CreateOrUpdateBudget\CreateOrUpdateBudgetCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateOrUpdateBudgetHandlerTest extends KernelTestCase
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
        $command = new CreateOrUpdateBudgetCommand(
            name: 'Complete Budget Test',
            amount: 1200.0,
            enabled: true
        );

        $this->messageBus->dispatch($command);

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
        $command->setOriginId($existingBudget->getId());

        $this->messageBus->dispatch($command);

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
