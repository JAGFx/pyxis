<?php

namespace App\Tests\Integration\Domain\Budget\Message\Command\CreateHistoryBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Command\CreateHistoryBudget\CreateHistoryBudgetCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\HistoryBudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class CreateHistoryBudgetHandlerTest extends KernelTestCase
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
        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create([
            'name'    => 'Test Budget',
            'amount'  => 1200.0,
            'enabled' => true,
        ])->_real();

        $command = new CreateHistoryBudgetCommand(
            budget: $budget,
            amount: 1200.0,
            date: new DateTimeImmutable(),
            spent: 900.0,
            relativeProgress: 0.75,
        );

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

        /** @var HistoryBudget $historyBudget */
        $historyBudget = HistoryBudgetFactory::new()->create([
            'date'             => new DateTimeImmutable(),
            'relativeProgress' => 0.5,
            'spent'            => 500.0,
            'amount'           => 1000.0,
            'budget'           => $budget,
        ])->_real();

        $this->objectMapper->map($historyBudget, CreateHistoryBudgetCommand::class);

        $this->expectNotToPerformAssertions();
    }
}
