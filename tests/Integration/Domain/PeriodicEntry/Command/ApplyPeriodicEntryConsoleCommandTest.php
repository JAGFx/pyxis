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
        $today             = new DateTimeImmutable();
        $currentDayOfMonth = $today->format('j');

        // Create dates with different days of month
        $differentDayOfMonth = '1' === $currentDayOfMonth ? '2' : '1';
        $anotherDifferentDay = '15' === $currentDayOfMonth ? '16' : '15';

        yield 'different_day_and_same_day' => [
            'scenarioName' => 'Different Day + Same Day',
            'entryDates'   => [
                $today->format('Y-m-') . $differentDayOfMonth, // Different day of month
                'now', // Same day
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'different_days_same_day_different_day' => [
            'scenarioName' => 'Different Days + Same Day + Different Day',
            'entryDates'   => [
                $today->format('Y-m-') . $differentDayOfMonth, // Different day
                'now', // Same day
                $today->format('Y-m-') . $anotherDifferentDay, // Another different day
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'mixed_days_with_multiple_same_day' => [
            'scenarioName' => 'Mixed Days + Multiple Same Day',
            'entryDates'   => [
                $today->format('Y-m-') . $differentDayOfMonth, // Different day
                'now', // Same day
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'same_day_different_months_years' => [
            'scenarioName' => 'Same Day Different Months/Years',
            'entryDates'   => [
                'now', // Today
                $today->modify('-1 month')->format('Y-m-') . $currentDayOfMonth, // Same day last month
                $today->modify('+1 month')->format('Y-m-') . $currentDayOfMonth, // Same day next month
                $today->modify('+1 year')->format('Y-m-') . $currentDayOfMonth, // Same day next year
            ],
            'expectedSuccessfulEntries' => 4, // All should pass as they have same day of month
        ];

        yield 'multiple_same_day_entries' => [
            'scenarioName'              => 'Multiple Same Day Entries',
            'entryDates'                => ['now', 'now', 'now'],
            'expectedSuccessfulEntries' => 3,
        ];

        yield 'mixed_day_schedule' => [
            'scenarioName' => 'Mixed Day Schedule',
            'entryDates'   => [
                $today->format('Y-m-') . $differentDayOfMonth, // Different day
                'now', // Same day
                $today->format('Y-m-') . $anotherDifferentDay, // Different day
                'now', // Same day
                $today->format('Y-m-') . ('10' === $currentDayOfMonth ? '11' : '10'), // Another different day
            ],
            'expectedSuccessfulEntries' => 2,
        ];

        yield 'complex_day_timeline' => [
            'scenarioName' => 'Complex Day Timeline',
            'entryDates'   => [
                $today->modify('-1 year')->format('Y-m-') . $differentDayOfMonth, // Different day, last year
                $today->modify('-1 month')->format('Y-m-') . $anotherDifferentDay, // Different day, last month
                $today->modify('-1 week')->format('Y-m-') . $differentDayOfMonth, // Different day, last week
                'now', // Same day, today
                $today->modify('+1 week')->format('Y-m-') . $differentDayOfMonth, // Different day, next week
                $today->modify('+1 month')->format('Y-m-') . $anotherDifferentDay, // Different day, next month
                $today->modify('+1 year')->format('Y-m-') . $differentDayOfMonth, // Different day, next year
            ],
            'expectedSuccessfulEntries' => 1,
        ];

        yield 'same_day_different_times' => [
            'scenarioName' => 'Same Day Different Times',
            'entryDates'   => [
                'now',
                $today->format('Y-m-d') . ' 10:00:00',
                $today->format('Y-m-d') . ' 15:30:00',
                $today->format('Y-m-d') . ' 23:59:59',
            ],
            'expectedSuccessfulEntries' => 4,
        ];

        yield 'alternating_day_pattern' => [
            'scenarioName' => 'Alternating Day Pattern',
            'entryDates'   => [
                'now', // Same day
                $today->format('Y-m-') . $differentDayOfMonth, // Different day
                'now', // Same day
                $today->format('Y-m-') . $anotherDifferentDay, // Different day
                'now', // Same day
                $today->format('Y-m-') . ('20' === $currentDayOfMonth ? '21' : '20'), // Different day
            ],
            'expectedSuccessfulEntries' => 3,
        ];

        yield 'no_entries_scheduled_for_today' => [
            'scenarioName' => 'No Entries Scheduled for Today',
            'entryDates'   => [
                $today->format('Y-m-') . $differentDayOfMonth, // Different day
                $today->format('Y-m-') . $anotherDifferentDay, // Different day
                $today->format('Y-m-') . ('25' === $currentDayOfMonth ? '26' : '25'), // Different day
                $today->format('Y-m-') . ('28' === $currentDayOfMonth ? '29' : '28'), // Different day
                $today->format('Y-m-') . ('30' === $currentDayOfMonth ? '31' : '30'), // Different day
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

        $periodicEntries = [];

        // Create periodic entries based on the scenario
        foreach ($entryDates as $index => $dateModifier) {
            $executionDate = new DateTimeImmutable($dateModifier);
            /** @var PeriodicEntry $periodicEntry */
            $periodicEntry = PeriodicEntryFactory::new()->create([
                'name'          => "{$scenarioName} - Entry {$index} ({$dateModifier})",
                'executionDate' => $executionDate,
                'account'       => $account,
                'amount'        => null, // Forecast type entry
            ])->_real();
            $periodicEntry->addBudget($budget);
            $periodicEntries[] = $periodicEntry;

            $this->entityManager->persist($periodicEntry);
        }

        $this->entityManager->flush();

        // Act - Execute the command
        $exitCode = $this->commandTester->execute([]);

        // Assert - Verify that the command completed successfully
        $this->assertEquals(Command::SUCCESS, $exitCode);

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
