<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
