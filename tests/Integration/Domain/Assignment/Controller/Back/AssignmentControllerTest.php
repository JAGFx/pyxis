<?php

namespace App\Tests\Integration\Domain\Assignment\Controller\Back;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AssignmentControllerTest extends WebTestCase
{
    public function testListShowsTheBalanceAndTheTarget(): void
    {
        $client = static::createClient();

        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Livret A'])->_real();
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create([
            'name'    => 'Vacances',
            'account' => $account,
            'amount'  => 0,
            'target'  => 200000,
        ])->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 30000, 'assignment' => $assignment]);

        $crawler = $client->request(Request::METHOD_GET, '/assignments');

        self::assertResponseIsSuccessful();
        $item = $crawler->filter('#assignment_list_item_' . $assignment->getId())->text();
        self::assertStringContainsString('300.00 €', $item);
        self::assertSame('/ 2 000.00 €', trim($crawler->filter('#assignment_list_item_' . $assignment->getId() . ' small.text-muted')->text()));
        self::assertStringContainsString('15%', $item);
    }

    public function testListShowsCompletedOnceTheTargetIsReached(): void
    {
        $client = static::createClient();

        /** @var Assignment $reached */
        $reached = AssignmentFactory::new()->create(['amount' => 200000, 'target' => 200000])->_real();
        /** @var Assignment $exceeded */
        $exceeded = AssignmentFactory::new()->create(['amount' => 250000, 'target' => 200000])->_real();

        $crawler = $client->request(Request::METHOD_GET, '/assignments');

        self::assertResponseIsSuccessful();
        foreach ([$reached, $exceeded] as $assignment) {
            $item = $crawler->filter('#assignment_list_item_' . $assignment->getId())->text();
            self::assertStringContainsString('Complet', $item);
            self::assertStringNotContainsString('%', $item);
        }
    }

    public function testListShowsNoProgressWithoutTarget(): void
    {
        $client = static::createClient();

        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['name' => 'Sans objectif', 'amount' => 160000, 'target' => null])->_real();

        $crawler = $client->request(Request::METHOD_GET, '/assignments');

        self::assertResponseIsSuccessful();
        $item = $crawler->filter('#assignment_list_item_' . $assignment->getId());
        self::assertStringContainsString('1 600.00 €', $item->text());
        self::assertStringNotContainsString('%', $item->text());
        self::assertStringNotContainsString(' / ', $item->text());
    }
}
