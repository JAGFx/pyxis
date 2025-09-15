<?php

namespace App\Shared\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'bugr:old-budget:budget-summary',
    description: 'Display budget periods with amount, start and end date.'
)]
class OldBudgetSummaryCommand extends Command
{
    private Connection $connection;
    private Connection $oldBugrManager;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();

        $this->connection = $this->entityManager->getConnection();

        /** @var Connection $oldBugrManager */
        $oldBugrManager       = $this->managerRegistry->getConnection('old_bugr');
        $this->oldBugrManager = $oldBugrManager;
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(shortcut: '-a')] ?string $account = null,
        #[Option(shortcut: '-b')] array $budgetIds = [],
    ): int {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $periodBalances = $this->getBudgetPeriodBalances();
        $balances       = $this->getBudgetGlobalBalances();

        if (!is_null($account) && [] !== $budgetIds) {
            $account = $this->entityManager
                ->getRepository(Account::class)
                ->findOneBy(['name' => $account]);

            if (is_null($account)) {
                $symfonyStyle->error('Account not found');

                return Command::FAILURE;
            }

            $balancesToUpdate = array_filter($balances, function (array $row) use ($budgetIds): bool {
                return in_array($row['budget_id'], $budgetIds);
            });

            foreach ($balancesToUpdate as $balanceToUpdate) {
                $budget = $this->entityManager
                    ->getRepository(Budget::class)
                    ->findOneBy(['name' => $balanceToUpdate['budget']]);

                if (is_null($budget)) {
                    $symfonyStyle->error('Budget not found');

                    return Command::FAILURE;
                }

                /** @var ?Entry $entryForecast */
                $entryForecast = $this->entityManager
                    ->createQuery('
                        SELECT e 
                        FROM App\Domain\Entry\Entity\Entry e 
                        JOIN e.budget b 
                        JOIN e.account a
                        WHERE e.name = :entryName 
                        AND b.name = :budgetName
                        AND a.id = :accountId
                    ')
                    ->setParameter('entryName', ImportOlderDataCommand::DEFAULT_BUDGET_NAME_FORCAST)
                    ->setParameter('budgetName', ImportOlderDataCommand::DEFAULT_BUDGET)
                    ->setParameter('accountId', $account->getId())
                    ->getOneOrNullResult();

                if (false === $entryForecast) {
                    $symfonyStyle->error('Forecast entry not found');

                    return Command::FAILURE;
                }

                $this->connection->insert('entry', [
                    'name'       => 'Import budget',
                    'amount'     => $balanceToUpdate['balance'],
                    'created_at' => new DateTimeImmutable('-1 year'),
                    'updated_at' => new DateTimeImmutable(),
                    'account_id' => $account->getId(),
                    'budget_id'  => $budget->getId(),
                ], [
                    'created_at' => Types::DATETIME_IMMUTABLE,
                    'updated_at' => Types::DATETIME_IMMUTABLE,
                ]);

                $entryForecast->setAmount($entryForecast->getAmount() - $balanceToUpdate['balance']);
                dump($entryForecast->getAmount(), $balanceToUpdate['balance']);
                $this->connection->update('entry', [
                    'amount' => $entryForecast->getAmount(),
                ], [
                    'id' => $entryForecast->getId(),
                ]);
            }
        }

        if ([] === $periodBalances) {
            $symfonyStyle->warning('No budget period balances found.');
        } else {
            $symfonyStyle->section('Budget Period Details');
            $symfonyStyle->table(
                ['Budget', 'Start Date', 'End Date', 'Budget Amount', 'Months', 'Prorated Amount', 'Total Spent'],
                array_map(function (array $row): array {
                    return [
                        $row['budget'],
                        $row['date_debut'],
                        $row['date_fin'] ?? 'Now',
                        $row['montant'] . ' €',
                        $row['months'] . ' months',
                        $row['prorated_amount'] . ' €',
                        $row['total_spent'] . ' €',
                    ];
                }, $periodBalances)
            );
        }

        if ([] === $balances) {
            $symfonyStyle->warning('No budget balances found.');
        } else {
            $symfonyStyle->section('Budget Global Balances');
            $symfonyStyle->table(
                ['Budget ID', 'Budget', 'Global Balance'],
                array_map(function (array $row): array {
                    return [
                        $row['budget_id'],
                        $row['budget'],
                        $row['balance'] . ' €',
                    ];
                }, $balances)
            );
        }

        return Command::SUCCESS;
    }

    private function getBudgetPeriodBalances(): array
    {
        /** @var Connection $oldBugrManager */
        $oldBugrManager = $this->managerRegistry->getConnection('old_bugr');
        $budgets        = $oldBugrManager->fetchAllAssociative('SELECT * FROM budget');

        $result = [];

        foreach ($budgets as $budget) {
            $periods      = $this->extractPeriodsFromBudget($budget);
            $splitPeriods = $this->splitPeriodsToMaxOneYear($periods, $budget['id']);

            foreach ($splitPeriods as $splitPeriod) {
                $result[] = [
                    'budget'          => $splitPeriod['budget'],
                    'date_debut'      => $splitPeriod['date_debut'],
                    'date_fin'        => $splitPeriod['date_fin'],
                    'montant'         => $splitPeriod['montant'],
                    'months'          => $splitPeriod['months'],
                    'prorated_amount' => $splitPeriod['prorated_amount'],
                    'total_spent'     => $splitPeriod['total_spent'],
                ];
            }
        }

        return $result;
    }

    private function getBudgetGlobalBalances(): array
    {
        $budgets = $this->oldBugrManager->fetchAllAssociative('SELECT * FROM budget');

        $result = [];

        foreach ($budgets as $budget) {
            $periods      = $this->extractPeriodsFromBudget($budget);
            $splitPeriods = $this->splitPeriodsToMaxOneYear($periods, $budget['id']);

            $globalBalance = 0.0;
            foreach ($splitPeriods as $splitPeriod) {
                $globalBalance += $splitPeriod['prorated_amount'] + $splitPeriod['total_spent'];
            }

            $result[] = [
                'budget_id' => $budget['id'],
                'budget'    => $budget['name'],
                'balance'   => round($globalBalance, 2),
            ];
        }

        // Sort by budget name alphabetically
        usort($result, function (array $a, array $b): int {
            return strcasecmp($a['budget'], $b['budget']);
        });

        return $result;
    }

    private function extractPeriodsFromBudget(array $budget): array
    {
        $historic = @unserialize($budget['historic']);
        $periods  = [];

        if (!is_array($historic) || [] === $historic) {
            $periods[] = [
                'budget'     => $budget['name'],
                'date_debut' => $budget['created_at'],
                'date_fin'   => null,
                'montant'    => (float) $budget['amount'],
            ];
        } else {
            $historicData = [];
            foreach ($historic as $entry) {
                if (isset($entry['date']) && isset($entry['amount'])) {
                    $dateTime       = $entry['date'];
                    $dateString     = is_object($dateTime) ? $dateTime->format('Y-m-d H:i:s') : (string) $dateTime;
                    $historicData[] = [
                        'date'      => $dateString,
                        'timestamp' => strtotime($dateString),
                        'amount'    => (float) $entry['amount'],
                    ];
                }
            }

            usort($historicData, function (array $a, array $b): int {
                return $a['timestamp'] - $b['timestamp'];
            });

            // First period
            if ([] !== $historicData) {
                $periods[] = [
                    'budget'     => $budget['name'],
                    'date_debut' => $budget['created_at'],
                    'date_fin'   => $historicData[0]['date'],
                    'montant'    => $historicData[0]['amount'],
                ];
            }

            // Intermediate periods
            for ($i = 0; $i < count($historicData) - 1; ++$i) {
                $periods[] = [
                    'budget'     => $budget['name'],
                    'date_debut' => $historicData[$i]['date'],
                    'date_fin'   => $historicData[$i + 1]['date'],
                    'montant'    => $historicData[$i + 1]['amount'],
                ];
            }

            // Last period
            if ([] !== $historicData) {
                $periods[] = [
                    'budget'     => $budget['name'],
                    'date_debut' => end($historicData)['date'],
                    'date_fin'   => null,
                    'montant'    => (float) $budget['amount'],
                ];
            }
        }

        return $periods;
    }

    private function splitPeriodsToMaxOneYear(array $periods, int $budgetId): array
    {
        $splitPeriods = [];

        // Fetch budget info to check if it is disabled
        $budgetInfo   = $this->oldBugrManager->fetchAssociative('SELECT enable, updated_at FROM budget WHERE id = :id', ['id' => $budgetId]);
        $isDisabled   = 0 === (int) $budgetInfo['enable'];
        $disabledDate = $isDisabled ? new DateTimeImmutable($budgetInfo['updated_at']) : null;

        foreach ($periods as $period) {
            $startDate = new DateTimeImmutable($period['date_debut']);
            $endDate   = $period['date_fin'] ? new DateTimeImmutable($period['date_fin']) : new DateTimeImmutable();

            $currentStart = clone $startDate;

            while ($currentStart < $endDate) {
                // Calculate end of current year (December 31)
                $endOfYear = new DateTimeImmutable($currentStart->format('Y') . '-12-31 23:59:59');

                // If end of year exceeds actual end of period, use actual end
                $currentEnd = $endOfYear > $endDate ? $endDate : $endOfYear;

                // Exclude current year
                if ((int) $currentStart->format('Y') >= (int) date('Y')) {
                    break;
                }

                // Exclude periods after the deactivation date
                if ($isDisabled && $currentStart >= $disabledDate) {
                    break;
                }

                // Calculate number of months in this sub-period
                $monthsInSubPeriod = $this->calculateMonthsBetweenDates($currentStart, $currentEnd);

                // Round the period to the next integer
                $roundedMonths = ceil($monthsInSubPeriod);

                // For a maximum period (12 months), use exactly the budget amount
                if ($roundedMonths >= 12) {
                    $roundedMonths  = 12;
                    $proRatedAmount = $period['montant'];
                } else {
                    // Calculate the prorated budget amount based on real months (not rounded)
                    $proRatedAmount = ($period['montant'] / 12) * $monthsInSubPeriod;
                }

                // Get the total entries for this sub-period
                $totalSpent = $this->getTotalSpentForPeriod($budgetId, $currentStart, $currentEnd);

                // Create the sub-period (even if 0 entries)
                $splitPeriods[] = [
                    'budget'          => $period['budget'],
                    'date_debut'      => $currentStart->format('Y-m-d H:i:s'),
                    'date_fin'        => $currentEnd == $endDate && null === $period['date_fin'] ? null : $currentEnd->format('Y-m-d H:i:s'),
                    'montant'         => $period['montant'], // Original amount
                    'months'          => $roundedMonths, // Number of months rounded up to the next integer
                    'prorated_amount' => round($proRatedAmount, 2), // Precise prorated amount (not rounded to integer)
                    'total_spent'     => $totalSpent,
                ];

                // Move to the beginning of the next year (January 1st)
                $currentStart = new DateTimeImmutable(($currentStart->format('Y') + 1) . '-01-01 00:00:00');
            }
        }

        return $splitPeriods;
    }

    private function calculateMonthsBetweenDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate): float
    {
        $start = clone $startDate;
        $end   = clone $endDate;

        // If it's the same date, return 0
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return 0;
        }

        $months = 0;

        // Calculate full months
        while ($start->format('Y-m') < $end->format('Y-m')) {
            ++$months;
            $start->add(new DateInterval('P1M'));
        }

        // Add fraction for the partial ending month if necessary
        if ($start->format('Y-m') === $end->format('Y-m')) {
            $daysInMonth = (int) $start->format('t');
            $daysDiff    = (int) $end->format('d') - (int) $start->format('d');
            if ($daysDiff > 0) {
                $months += $daysDiff / $daysInMonth;
            }
        }

        return $months;
    }

    private function getTotalSpentForPeriod(int $budgetId, DateTimeImmutable $startDate, DateTimeImmutable $endDate): float
    {
        $query  = 'SELECT SUM(amount) as total_spent FROM entry WHERE budget_id = :budget_id AND date >= :date_debut';
        $params = [
            'budget_id'  => $budgetId,
            'date_debut' => $startDate->format('Y-m-d H:i:s'),
        ];

        // Always add end condition to avoid counting the same entries multiple times
        $query .= ' AND date < :date_fin';
        $params['date_fin'] = $endDate->format('Y-m-d H:i:s');

        $entryResult = $this->oldBugrManager->fetchAssociative($query, $params);

        return (float) ($entryResult['total_spent'] ?? 0);
    }
}
