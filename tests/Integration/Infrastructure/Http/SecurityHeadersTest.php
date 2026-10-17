<?php

namespace App\Tests\Integration\Infrastructure\Http;

use App\Infrastructure\Cqs\EventListener\CorrelationIdListener;
use App\Infrastructure\Http\EventListener\SecurityHeadersListener;
use App\Tests\Integration\Shared\WebTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class SecurityHeadersTest extends WebTestCase
{
    public static function pathsDataset(): Generator
    {
        yield 'HTML page' => ['/budgets', true];
        yield 'Turbo stream' => ['/entries/balance', true];
        // In debug only, Symfony's ErrorListener removes the CSP so the exception page can run its inline scripts
        yield 'Error page' => ['/search-form?target=unknown', false];
    }

    #[DataProvider('pathsDataset')]
    public function testSecurityHeadersAreSent(string $path, bool $withCsp): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $path);

        foreach (SecurityHeadersListener::HEADERS as $name => $value) {
            if (!$withCsp && 'Content-Security-Policy' === $name) {
                continue;
            }

            self::assertResponseHeaderSame($name, $value);
        }
    }

    public function testValidCorrelationIdIsKept(): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/budgets', server: ['HTTP_X_CORRELATION_ID' => 'req-42.abc_DEF']);

        self::assertResponseHeaderSame(CorrelationIdListener::HEADER_NAME, 'req-42.abc_DEF');
    }

    public static function invalidCorrelationIdsDataset(): Generator
    {
        yield 'Too long' => [str_repeat('a', 65)];
        yield 'Spaces' => ['forged id'];
        yield 'Log injection' => ['abc"}{"level":"critical'];
        yield 'Empty' => [''];
    }

    #[DataProvider('invalidCorrelationIdsDataset')]
    public function testInvalidCorrelationIdIsReplaced(string $correlationId): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/budgets', server: ['HTTP_X_CORRELATION_ID' => $correlationId]);

        $sent = (string) $client->getResponse()->headers->get(CorrelationIdListener::HEADER_NAME);
        self::assertNotSame($correlationId, $sent);
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $sent, 'A new ULID is expected');
    }
}
