<?php

namespace App\Tests\Unit\Shared\Message\Command\GetBudgetAccountBalance;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\ApplyBudgetAccountBalance\ApplyBudgetAccountBalanceCommand;
use App\Tests\Unit\Shared\BudgetTestTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GetBudgetAccountBalanceHandlerTest extends TestCase
{
    use BudgetTestTrait;

    private const int BUDGET_AMOUNT = 100000;

    private EntityManagerInterface $entityManager;

    private MessageBus $messageBus;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus    = $this->createMock(MessageBus::class);
    }

    public function testBudgetWithBalancedCashFlowDoNothing(): void
    {
        $progress = 20000;
        $cashFlow = 0;
        $budget   = $this->generateBudget([
            'amount'  => self::BUDGET_AMOUNT,
            'entries' => [
                [
                    'entryName'      => 'Past year entry',
                    'entryAmount'    => -self::BUDGET_AMOUNT,
                    'entryCreatedAt' => new DateTimeImmutable('-1 year'),
                ],
                [
                    'entryName'      => 'Past year entry',
                    'entryAmount'    => self::BUDGET_AMOUNT,
                    'entryCreatedAt' => new DateTimeImmutable('-1 year -1 hour'),
                ],
                [
                    'entryAmount' => 20000,
                ],
            ],
        ]);

        $this->entityManager
             ->expects(self::never())
             ->method('flush');

        $this->messageBus->dispatch(new ApplyBudgetAccountBalanceCommand($budget, new Account()));

        self::assertCount(3, $budget->getEntries());
        self::assertSame($progress, $budget->getProgress());
        self::assertSame($cashFlow, $budget->getCashFlow());
    }
}
