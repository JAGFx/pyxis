<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Integration\Shared\WebTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class AnonymousSmokeTest extends WebTestCase
{
    public static function routesDatasets(): Generator
    {
        yield ['/'];
        yield ['/transfer'];
        yield ['/search-form?target=account'];
        yield ['/accounts'];
        yield ['/accounts/create'];
        yield ['/assignments'];
        yield ['/assignments/create'];
        yield ['/budgets'];
        yield ['/budgets/create'];
        yield ['/entries'];
        yield ['/entries/search'];
        yield ['/entries/search?page=2'];
        yield ['/entries/create'];
        yield ['/entries/balance'];
        yield ['/periodic_entries'];
        yield ['/periodic_entries/create'];

        // Tampered pagination falls back to the first page instead of a TypeError
        yield ['/entries?page=abc'];
        yield ['/entries?page=-3'];
        yield ['/entries?page=99999999999999999999'];
    }

    public static function notFoundRoutesDatasets(): Generator
    {
        yield ['/search-form?target=unknown'];
        yield ['/search-form'];
    }

    #[DataProvider('notFoundRoutesDatasets')]
    public function testRoutesIsNotFound(string $path): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $path);

        self::assertResponseStatusCodeSame(404, "Bad status code for path: $path");
    }

    #[DataProvider('routesDatasets')]
    public function testRoutesIsOk(string $path): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $path);

        $status = $client->getResponse()->getStatusCode();
        if ($status >= 300 && $status < 400) {
            $client->followRedirect();
        }

        self::assertResponseIsSuccessful("Bad status code for path: $path");
    }
}
