<?php

namespace App\Tests\Integration\Shared;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

abstract class CommandHandlerTestCase extends KernelTestCase
{
    use InteractsWithMessenger;

    protected MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->messageBus = $container->get(MessageBus::class);
    }

    protected function assertAsyncDispatchSuccessfully(): void
    {
        $this->transport('async')->queue()->assertCount(1);
        $this->transport('async')->process();
        $this->transport('async')->rejected()->assertEmpty();
    }

    protected function assertArtifactIsFinished(Artifact $artifact, string $expectedCommandName): void
    {
        self::assertSame($expectedCommandName, $artifact->getCommand());
        self::assertNotNull($artifact->getUuid());
        self::assertNotNull($artifact->getDocumentName());
        self::assertNotNull($artifact->getDocumentPath());
        self::assertNotNull($artifact->getFinishedAt());
    }
}
