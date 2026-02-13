<?php

namespace App\Tests\Integration\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact\AttachDocumentToArtifactCommand;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Tests\Factory\ArtifactFactory;
use App\Tests\Integration\Shared\CommandHandlerTestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class AttachDocumentToArtifactHandler extends CommandHandlerTestCase
{
    private const string ARTIFACT_UUID_DEFAULT_1 = '00000000-0000-0000-0000-000000000001';

    private const string ARTIFACT_UUID_PARENT = '00000000-0000-0000-0000-000000000002';

    private const string ARTIFACT_COMMAND_NAME = 'export_command';

    private function generateAttachDocumentToArtifactCommand(array $data = []): AttachDocumentToArtifactCommand
    {
        return new AttachDocumentToArtifactCommand(
            artifactUuid: Uuid::fromString($data['artifactUuid']),
            documentName: $data['documentName'] ?? 'documentName',
            documentPath: $data['documentPath'] ?? 'documentPath',
            storage: $data['storage'] ?? StorageEnum::FILE_SYSTEM,
            parentArtifactUuid: $data['parentArtifactUuid'] ?? null,
        );
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function testAttachDocumentWithParent(): void
    {
        ArtifactFactory::new()->create([
            'uuid'    => Uuid::fromString(self::ARTIFACT_UUID_DEFAULT_1),
            'command' => self::ARTIFACT_COMMAND_NAME,
        ]);
        ArtifactFactory::new()->create([
            'uuid'    => Uuid::fromString(self::ARTIFACT_UUID_PARENT),
            'command' => self::ARTIFACT_COMMAND_NAME,
        ]);

        $command = $this->generateAttachDocumentToArtifactCommand([
            'artifactUuid'       => self::ARTIFACT_UUID_DEFAULT_1,
            'parentArtifactUuid' => self::ARTIFACT_UUID_PARENT,
        ]);
        $this->messageBus->dispatch($command);

        /** @var Artifact $artifactBase */
        $artifactBase = ArtifactFactory::find([
            'uuid' => Uuid::fromString(self::ARTIFACT_UUID_DEFAULT_1),
        ])->_real();

        $this->assertArtifactIsFinished($artifactBase, self::ARTIFACT_COMMAND_NAME);
        self::assertSame(self::ARTIFACT_UUID_PARENT, $artifactBase->getParent()->getUuid()->toRfc4122());
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function testAttachDocumentWithoutParent(): void
    {
        ArtifactFactory::new()->create([
            'uuid'    => Uuid::fromString(self::ARTIFACT_UUID_DEFAULT_1),
            'command' => self::ARTIFACT_COMMAND_NAME,
        ]);
        ArtifactFactory::new()->create([
            'uuid'    => Uuid::fromString(self::ARTIFACT_UUID_PARENT),
            'command' => self::ARTIFACT_COMMAND_NAME,
        ]);

        $command = $this->generateAttachDocumentToArtifactCommand([
            'artifactUuid' => self::ARTIFACT_UUID_DEFAULT_1,
        ]);
        $this->messageBus->dispatch($command);

        /** @var Artifact $artifactBase */
        $artifactBase = ArtifactFactory::find([
            'uuid' => Uuid::fromString(self::ARTIFACT_UUID_DEFAULT_1),
        ])->_real();

        $this->assertArtifactIsFinished($artifactBase, self::ARTIFACT_COMMAND_NAME);
        self::assertNull($artifactBase->getParent());
    }
}
