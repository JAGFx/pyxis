<?php

namespace App\Tests\Integration\Domain\Account\Controller\Back;

use App\Tests\Factory\AccountFactory;
use App\Tests\Integration\Shared\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountControllerTest extends WebTestCase
{
    public function testCreateWithValidNameRedirectsToList(): void
    {
        $client = static::createClient();
        $client->setServerParameter('HTTP_ORIGIN', 'http://localhost');

        $crawler = $client->request(Request::METHOD_GET, '/accounts/create');
        $client->submit($crawler->filter('form')->form(['account_create_or_update[name]' => 'Livret jeune']));

        self::assertResponseRedirects('/accounts');
        self::assertCount(1, AccountFactory::repository()->findBy(['name' => 'Livret jeune']));
    }

    public function testCreateWithTooLongNameIsRejectedByTheForm(): void
    {
        $client = static::createClient();
        $client->setServerParameter('HTTP_ORIGIN', 'http://localhost');

        $crawler = $client->request(Request::METHOD_GET, '/accounts/create');
        $client->submit($crawler->filter('form')->form(['account_create_or_update[name]' => str_repeat('a', 256)]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertCount(0, AccountFactory::repository()->findAll());
    }
}
