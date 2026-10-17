<?php

namespace App\Tests\Integration\Shared\Components;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\WebTestCase;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Anonymous component props (Button, Card, Tooltip, MetricOverviewItem, GridActionButton) must not be rendered
 * as HTML attributes: `title` would even add a native browser tooltip next to the Flowbite one.
 */
class ComponentPropsTest extends WebTestCase
{
    private const string LEAKED_PROPS_SELECTOR = '[icon], [label], [actionurl], [node], [dir], [placement], '
        . 'div[value], div[color], div[size], a[color], a[size], button[color], button[size], [data-tooltip-target][title]';

    public static function pathsDataset(): Generator
    {
        yield 'Budgets (Button, Tooltip, GridActionButton)' => ['/budgets'];
        yield 'Assignments' => ['/assignments'];
        yield 'Entries' => ['/entries'];
        yield 'Accounts' => ['/accounts'];
        yield 'Budget form (Card)' => ['/budgets/create'];
        yield 'Account cash flow (MetricOverviewItem)' => ['/accounts/%d/cash-flow'];
        yield 'Global balance (MetricOverviewItem)' => ['/entries/balance'];
    }

    #[DataProvider('pathsDataset')]
    public function testPropsAreNotRenderedAsAttributes(string $path): void
    {
        $client = static::createClient();

        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Livret A'])->_real();
        /** @var Budget $budget */
        $budget = BudgetFactory::new()->create(['name' => 'Vacances', 'amount' => 600.0])->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 1000.0, 'name' => 'Salaire']);
        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -100.0,
            'name'      => 'Billets',
            'createdAt' => new DateTimeImmutable('-1 year'),
        ]);
        AssignmentFactory::new()->create(['account' => $account, 'amount' => 100.0, 'target' => 500.0]);

        $crawler = $client->request(Request::METHOD_GET, sprintf($path, $account->getId()));

        self::assertResponseIsSuccessful();
        $leaked = $crawler->filter(self::LEAKED_PROPS_SELECTOR)->each(
            static fn ($node): string => $node->outerHtml()
        );
        self::assertSame([], $leaked, 'Component props rendered as HTML attributes');
    }
}
