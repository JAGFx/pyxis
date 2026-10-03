<?php

namespace App\Tests\Integration\Domain\PeriodicEntry\Controller\Back;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

class PeriodicEntryControllerTest extends WebTestCase
{
    private const string FORM_NAME = 'periodic_entry_create_or_update';

    public function testCreatePagePreselectsFirstAccountAndRendersItsAssignments(): void
    {
        $client = static::createClient();

        /** @var Account $firstAccount */
        $firstAccount = AccountFactory::new()->create(['name' => 'Alpha', 'enabled' => true])->_real();
        /** @var Account $secondAccount */
        $secondAccount = AccountFactory::new()->create(['name' => 'Beta', 'enabled' => true])->_real();
        AssignmentFactory::new()->create(['account' => $firstAccount, 'name' => 'Alpha assignment']);
        AssignmentFactory::new()->create(['account' => $secondAccount, 'name' => 'Beta assignment']);

        $crawler = $client->request(Request::METHOD_GET, '/periodic_entries/create');

        self::assertResponseIsSuccessful();
        self::assertSame(
            (string) $firstAccount->getId(),
            $crawler->filter(sprintf('select[name="%s[account]"] option[selected]', self::FORM_NAME))->attr('value')
        );

        $assignmentOptions = $crawler
            ->filter(sprintf('select[name="%s[assignment]"] option', self::FORM_NAME))
            ->each(static fn (Crawler $option): string => trim($option->text()));

        self::assertContains('Alpha assignment', $assignmentOptions);
        self::assertNotContains('Beta assignment', $assignmentOptions);
    }

    public function testAssignmentFieldIsRenderedRightAfterAmount(): void
    {
        $client = static::createClient();

        AccountFactory::new()->create(['name' => 'Alpha', 'enabled' => true]);

        $crawler = $client->request(Request::METHOD_GET, '/periodic_entries/create');

        self::assertResponseIsSuccessful();

        $fieldNames = $crawler
            ->filter('form [name^="' . self::FORM_NAME . '["]')
            ->each(static fn (Crawler $field): string => (string) $field->attr('name'));

        $amountPosition = array_search(self::FORM_NAME . '[amount]', $fieldNames, true);

        self::assertNotFalse($amountPosition);
        self::assertSame(self::FORM_NAME . '[assignment]', $fieldNames[$amountPosition + 1] ?? null);
    }

    public function testEditPagePreselectsCurrentAssignment(): void
    {
        $client = static::createClient();

        /** @var Account $account */
        $account = AccountFactory::new()->create(['name' => 'Alpha', 'enabled' => true])->_real();
        AssignmentFactory::new()->create(['account' => $account, 'name' => 'Other assignment']);
        /** @var Assignment $assignment */
        $assignment = AssignmentFactory::new()->create(['account' => $account, 'name' => 'Current assignment'])->_real();

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'account'    => $account,
            'amount'     => 20.0,
            'assignment' => $assignment,
        ])->_real();

        $crawler = $client->request(Request::METHOD_GET, sprintf('/periodic_entries/%d/update', $periodicEntry->getId()));

        self::assertResponseIsSuccessful();
        self::assertSame(
            (string) $assignment->getId(),
            $crawler->filter(sprintf('select[name="%s[assignment]"] option[selected]', self::FORM_NAME))->attr('value')
        );
    }
}
