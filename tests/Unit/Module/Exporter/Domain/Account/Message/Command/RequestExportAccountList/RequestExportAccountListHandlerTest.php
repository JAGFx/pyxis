<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListHandler;
use App\Module\Exporter\Domain\Artifact\Mailer\ArtifactMailerDispatcher;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

class RequestExportAccountListHandlerTest extends TestCase
{
    private DocumentFactoryResolver|MockObject $documentFactoryResolverMock;

    private EntityManagerInterface|MockObject $entityManagerMock;

    private MessageBus|MockObject $messageBusMock;

    private ArtifactMailerDispatcher|MockObject $artifactMailerDispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentFactoryResolverMock  = $this->createMock(DocumentFactoryResolver::class);
        $this->entityManagerMock            = $this->createMock(EntityManagerInterface::class);
        $this->messageBusMock               = $this->createMock(MessageBus::class);
        $this->artifactMailerDispatcherMock = $this->createMock(ArtifactMailerDispatcher::class);
    }

    private function generateRequestExportAccountListHandler(): RequestExportAccountListHandler
    {
        return new RequestExportAccountListHandler(
            $this->documentFactoryResolverMock,
            $this->messageBusMock,
            $this->entityManagerMock,
            $this->artifactMailerDispatcherMock
        );
    }

    /**
     * @throws CannotInsertRecord
     * @throws Throwable
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFirstHandleMustNotGenerateOrAttachDocument(): void
    {
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist');

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch');

        $this->documentFactoryResolverMock
            ->expects($this->never())
            ->method('resolve');

        $this->artifactMailerDispatcherMock
            ->expects($this->never())
            ->method('requestExportFinished');

        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV);
        $handler = $this->generateRequestExportAccountListHandler();
        $handler->__invoke($command);

        self::assertTrue($command->isOnExportingStage());
    }

    /**
     * @throws Throwable
     * @throws CannotInsertRecord
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSecondHandleMustNotGenerateArtefact(): void
    {
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');

        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch');

        $this->documentFactoryResolverMock
            ->expects($this->once())
            ->method('resolve');

        $this->artifactMailerDispatcherMock
            ->expects($this->once())
            ->method('requestExportFinished');

        $uuid    = Uuid::v7();
        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV)->setParentArtifactUuid($uuid);
        $handler = $this->generateRequestExportAccountListHandler();
        $handler->__invoke($command);

        self::assertSame($uuid->toRfc4122(), $command->getParentArtifactUuid()->toRfc4122());
    }
}
