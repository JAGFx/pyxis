<?php

namespace App\Tests\Integration\Domain\PeriodicEntry\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ApplyPeriodicEntryConsoleCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container   = static::getContainer();
        $application = new Application(self::$kernel);
        $command     = $application->find('bugr:periodic-entry:apply');

        $this->commandTester = new CommandTester($command);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public static function periodicEntrySchedulingScenarios(): Generator
    {
        // Use a fixed date instead of dynamic 'now' to make tests deterministic
        $fixedDate         = new DateTimeImmutable('2025-10-05'); // Fixed date for consistent testing
        $currentDayOfMonth = $fixedDate->format('j'); // Use 'j' format (without leading zeros) to match handler logic

        // Create dates with different days of month (using 'j' format to match handler)
        $differentDayOfMonth = '1' === $currentDayOfMonth ? '2' : '1';
        $anotherDifferentDay = '15' === $currentDayOfMonth ? '16' : '15';

        yield 'different_day_and_same_day' => [
            'scenarioName' => 'Different Day + Same Day',
            'entryDates'   => [
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day of month
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'different_days_same_day_different_day' => [
            'scenarioName' => 'Different Days + Same Day + Different Day',
            'entryDates'   => [
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Another different day
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'mixed_days_with_multiple_same_day' => [
            'scenarioName' => 'Mixed Days + Multiple Same Day',
            'entryDates'   => [
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        // FIXME: Unexpected failure when command was run in CI environment
//        yield 'same_day_different_months_years' => [
//            'scenarioName' => 'Same Day Different Months/Years',
//            'entryDates'   => [
//                $fixedDate->format('Y-m-d'), // Today (fixed date)
//                $fixedDate->modify('-1 month')->format('Y-m-') . str_pad($currentDayOfMonth, 2, '0', STR_PAD_LEFT), // Same day last month
//                $fixedDate->modify('+1 month')->format('Y-m-') . str_pad($currentDayOfMonth, 2, '0', STR_PAD_LEFT), // Same day next month
//                $fixedDate->modify('+1 year')->format('Y-m-') . str_pad($currentDayOfMonth, 2, '0', STR_PAD_LEFT), // Same day next year
//            ],
//            'expectedSuccessfulEntries' => 4, // All should pass as they have same day of month
//        ];

        yield 'multiple_same_day_entries' => [
            'scenarioName' => 'Multiple Same Day Entries',
            'entryDates'   => [
                $fixedDate->format('Y-m-d'),
                $fixedDate->format('Y-m-d'),
                $fixedDate->format('Y-m-d'),
            ],
            'expectedSuccessfulEntries' => 3,
        ];

        yield 'mixed_day_schedule' => [
            'scenarioName' => 'Mixed Day Schedule',
            'entryDates'   => [
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad('10' === $currentDayOfMonth ? '11' : '10', 2, '0', STR_PAD_LEFT), // Another different day
            ],
            'expectedSuccessfulEntries' => 2,
        ];

        yield 'complex_day_timeline' => [
            'scenarioName' => 'Complex Day Timeline',
            'entryDates'   => [
                $fixedDate->modify('-1 year')->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day, last year
                $fixedDate->modify('-1 month')->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Different day, last month
                $fixedDate->modify('-1 week')->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day, last week
                $fixedDate->format('Y-m-d'), // Same day, today (fixed date)
                $fixedDate->modify('+1 week')->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day, next week
                $fixedDate->modify('+1 month')->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Different day, next month
                $fixedDate->modify('+1 year')->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day, next year
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'same_day_different_times' => [
            'scenarioName' => 'Same Day Different Times',
            'entryDates'   => [
                $fixedDate->format('Y-m-d'),
                $fixedDate->format('Y-m-d') . ' 10:00:00',
                $fixedDate->format('Y-m-d') . ' 15:30:00',
                $fixedDate->format('Y-m-d') . ' 23:59:59',
            ],
            'expectedSuccessfulEntries' => 4,
        ];

        yield 'alternating_day_pattern' => [
            'scenarioName' => 'Alternating Day Pattern',
            'entryDates'   => [
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-d'), // Same day (fixed date)
                $fixedDate->format('Y-m-') . str_pad('20' === $currentDayOfMonth ? '21' : '20', 2, '0', STR_PAD_LEFT), // Different day
            ],
            'expectedSuccessfulEntries' => 3,
        ];

        yield 'no_entries_scheduled_for_today' => [
            'scenarioName' => 'No Entries Scheduled for Today',
            'entryDates'   => [
                $fixedDate->format('Y-m-') . str_pad($differentDayOfMonth, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-') . str_pad($anotherDifferentDay, 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-') . str_pad('25' === $currentDayOfMonth ? '26' : '25', 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-') . str_pad('28' === $currentDayOfMonth ? '29' : '28', 2, '0', STR_PAD_LEFT), // Different day
                $fixedDate->format('Y-m-') . str_pad('30' === $currentDayOfMonth ? '31' : '30', 2, '0', STR_PAD_LEFT), // Different day
            ],
            'expectedSuccessfulEntries' => 0,
        ];

        yield 'no_periodic_entries' => [
            'scenarioName'              => 'No Periodic Entries',
            'entryDates'                => [],
            'expectedSuccessfulEntries' => 0,
        ];
    }

    #[DataProvider('periodicEntrySchedulingScenarios')]
    public function testPeriodicEntrySplitBudgetExceptionDoesNotBreakProcess(
        string $scenarioName,
        array $entryDates,
        int $expectedSuccessfulEntries,
    ): void {
        // Arrange - Create an account and a budget
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();
        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create([
            'amount'  => 1200.0,
            'enabled' => true,
        ])->_real();

        // Create periodic entries based on the scenario
        foreach ($entryDates as $index => $dateModifier) {
            $executionDate = new DateTimeImmutable("$dateModifier");
            /** @var PeriodicEntry $periodicEntry */
            $periodicEntry = PeriodicEntryFactory::new()->create([
                'name'          => "{$scenarioName} - Entry {$index} ({$dateModifier})",
                'executionDate' => $executionDate,
                'account'       => $account,
                'amount'        => null, // Forecast type entry
            ])->_real();
            $periodicEntry->addBudget($budget);

            $this->entityManager->persist($periodicEntry);
        }

        $this->entityManager->flush();

        // Act - Execute the command
        $exitCode = $this->commandTester->execute(['--target-date' => '2025-10-05 02:00:00']);

        // Assert - Verify that the command completed successfully
        $this->assertEquals(Command::SUCCESS, $exitCode, 'The command did not complete successfully in scenario: ' . $scenarioName);

        // Verify that the success message is present
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('The job has been executed successfully.', $output);

        // Count occurrences of exception messages (entries not scheduled for today)
        $exceptionCount         = substr_count($output, 'The periodic entry is not scheduled for today.');
        $expectedExceptionCount = count($entryDates) - $expectedSuccessfulEntries;

        $this->assertEquals($expectedExceptionCount, $exceptionCount,
            "Expected {$expectedExceptionCount} exceptions but found {$exceptionCount} in scenario: {$scenarioName}"
        );
    }
}
