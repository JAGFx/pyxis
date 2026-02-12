<?php

namespace App\Tests\Integration\Shared;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Tests\Factory\ArtifactFactory;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class CommandHandlerTestCase extends KernelTestCase
{
    use InteractsWithMessenger;

    private MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->messageBus = $container->get(MessageBus::class);
    }

    protected function assertDispatchSuccessfully(CommandInterface $command): void
    {
        $this->messageBus->dispatch($command);

        $this->transport('async')->queue()->assertCount(1);
        $this->transport('async')->process();
        $this->transport('async')->rejected()->assertEmpty();
    }

    protected function assertArtifactExist(string $expectedCommandName): void
    {
        self::assertSame(1, ArtifactFactory::count());
        /** @var Artifact $firstArtifact */
        $firstArtifact = ArtifactFactory::first()->_real();

        self::assertSame($expectedCommandName, $firstArtifact->getCommand());
        self::assertNotNull($firstArtifact->getUuid());
        self::assertNotNull($firstArtifact->getFinishedAt());
        self::assertNotNull($firstArtifact->getDocumentName());
        self::assertNotNull($firstArtifact->getDocumentPath());
    }
}
