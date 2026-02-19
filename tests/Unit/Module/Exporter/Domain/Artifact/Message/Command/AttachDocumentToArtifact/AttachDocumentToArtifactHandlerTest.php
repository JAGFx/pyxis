<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact\AttachDocumentToArtifactCommand;
use App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact\AttachDocumentToArtifactHandler;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class AttachDocumentToArtifactHandlerTest extends TestCase
{
    private EntityFinder $entityFinderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFinderMock = $this->createMock(EntityFinder::class);
    }

    public function generateAttachDocumentToArtifactHandler(): AttachDocumentToArtifactHandler
    {
        return new AttachDocumentToArtifactHandler(
            $this->entityFinderMock,
            $this->createMock(EntityManagerInterface::class)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testAttachToAlreadyFinishedMustThrowAnException(): void
    {
        $this->entityFinderMock
            ->expects($this->once())
            ->method('findByUuidIdentifierOrFail')
            ->willReturn(new Artifact('export_command')->setFinishedAt(new DateTimeImmutable()));

        self::expectException(LogicException::class);
        self::expectExceptionMessage('Unable to attach document to this artifact: Already finished.');

        $command = new AttachDocumentToArtifactCommand(
            'some-uuid',
            'export_command',
            'document.pdf',
            '/path/to/document.pdf',
            DocumentTypeEnum::PDF,
            StorageEnum::FILE_SYSTEM
        );
        $this->generateAttachDocumentToArtifactHandler()
            ->__invoke($command);
    }
}
