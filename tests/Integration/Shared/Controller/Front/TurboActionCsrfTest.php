<?php

namespace App\Tests\Integration\Shared\Controller\Front;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions triggered by Turbo links (`data-turbo-method`) must not be reachable with GET
 * and must carry a same-origin request with the Turbo CSRF header.
 */
class TurboActionCsrfTest extends WebTestCase
{
    private const string ORIGIN = 'http://localhost';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public static function turboActionsDataset(): Generator
    {
        yield 'Remove entry' => ['entry', '/entries/%d/remove', Request::METHOD_DELETE];
        yield 'Remove assignment' => ['assignment', '/assignments/%d/remove', Request::METHOD_DELETE];
        yield 'Remove periodic entry' => ['periodic_entry', '/periodic_entries/%d/remove', Request::METHOD_DELETE];
        yield 'Toggle account' => ['account', '/accounts/%d/toggle', Request::METHOD_POST];
        yield 'Toggle budget' => ['budget', '/budgets/%d/toggle', Request::METHOD_POST];
    }

    #[DataProvider('turboActionsDataset')]
    public function testGetIsNotAllowed(string $subject, string $path, string $method): void
    {
        $id = $this->createSubject($subject);

        $this->client->request(Request::METHOD_GET, sprintf($path, $id));

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertTrue($this->isUnchanged($subject, $id));
    }

    public static function invalidCsrfDataset(): Generator
    {
        foreach (self::turboActionsDataset() as $name => [$subject, $path, $method]) {
            yield $name . ' without origin' => [$subject, $path, $method, [
                'HTTP_X_CSRF_TOKEN' => 'csrf-token',
            ]];
            yield $name . ' from another origin' => [$subject, $path, $method, [
                'HTTP_ORIGIN'       => 'https://evil.example',
                'HTTP_X_CSRF_TOKEN' => 'csrf-token',
            ]];
            yield $name . ' without CSRF header' => [$subject, $path, $method, [
                'HTTP_ORIGIN' => self::ORIGIN,
            ]];
        }
    }

    /**
     * @param array<string, string> $server
     */
    #[DataProvider('invalidCsrfDataset')]
    public function testInvalidCsrfIsRejected(string $subject, string $path, string $method, array $server): void
    {
        $id = $this->createSubject($subject);

        $this->client->request($method, sprintf($path, $id), server: $server);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertTrue($this->isUnchanged($subject, $id));
    }

    #[DataProvider('turboActionsDataset')]
    public function testSameOriginWithCsrfHeaderIsApplied(string $subject, string $path, string $method): void
    {
        $id = $this->createSubject($subject);

        $this->client->request($method, sprintf($path, $id), server: [
            'HTTP_ORIGIN'       => self::ORIGIN,
            'HTTP_X_CSRF_TOKEN' => 'csrf-token',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/vnd.turbo-stream.html; charset=UTF-8');
        self::assertFalse($this->isUnchanged($subject, $id));
    }

    private function createSubject(string $subject): int
    {
        /** @var Account $account */
        $account = AccountFactory::createOne()->_real();

        $entity = match ($subject) {
            'entry'          => EntryFactory::createOne(['account' => $account])->_real(),
            'assignment'     => AssignmentFactory::createOne(['account' => $account])->_real(),
            'periodic_entry' => PeriodicEntryFactory::createOne(['account' => $account])->_real(),
            'account'        => $account,
            'budget'         => BudgetFactory::createOne()->_real(),
        };

        return (int) $entity->getId();
    }

    private function isUnchanged(string $subject, int $id): bool
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return match ($subject) {
            'entry'          => $entityManager->find(Entry::class, $id) instanceof Entry,
            'assignment'     => $entityManager->find(Assignment::class, $id) instanceof Assignment,
            'periodic_entry' => $entityManager->find(PeriodicEntry::class, $id) instanceof PeriodicEntry,
            'account'        => (bool) $entityManager->find(Account::class, $id)?->isEnabled(),
            'budget'         => (bool) $entityManager->find(Budget::class, $id)?->isEnabled(),
        };
    }
}
