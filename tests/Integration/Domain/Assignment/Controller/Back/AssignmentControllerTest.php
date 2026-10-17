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
            'amount'  => 0.0,
            'target'  => 2000.0,
        ])->_real();
        EntryFactory::new()->create(['account' => $account, 'amount' => 300.0, 'assignment' => $assignment]);

        $crawler = $client->request(Request::METHOD_GET, '/assignments');

        self::assertResponseIsSuccessful();
        $item = $crawler->filter('#assignment_list_item_' . $assignment->getId())->text();
        self::assertStringContainsString('300.00 €', $item);
        self::assertStringContainsString('2 000.00 €', $item);
        self::assertStringContainsString('(15%)', $item);
    }
}
