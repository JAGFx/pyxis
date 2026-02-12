<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListHandler;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;

class RequestExportAccountListHandlerTest extends TestCase
{
    private DocumentFactoryResolver|MockObject $documentFactoryResolverMock;

    private EntityManagerInterface|MockObject $entityManagerMock;

    private MessageBus|MockObject $messageBusMock;

    private MailerInterface|MockObject $mailerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentFactoryResolverMock = $this->createMock(DocumentFactoryResolver::class);
        $this->entityManagerMock           = $this->createMock(EntityManagerInterface::class);
        $this->messageBusMock              = $this->createMock(MessageBus::class);
        $this->mailerMock                  = $this->createMock(MailerInterface::class);
    }

    private function generateRequestExportAccountListHandler(): RequestExportAccountListHandler
    {
        return new RequestExportAccountListHandler(
            $this->documentFactoryResolverMock,
            $this->messageBusMock,
            $this->mailerMock,
            $this->entityManagerMock,
        );
    }

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

        $this->mailerMock
            ->expects($this->never())
            ->method('send');

        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV);
        $handler = $this->generateRequestExportAccountListHandler();
        $handler->__invoke($command);

        self::assertTrue($command->isOnExportingStage());
    }

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

        $this->mailerMock
            ->expects($this->once())
            ->method('send');

        $uuid    = Uuid::v7();
        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV)->setArtifactUuid($uuid);
        $handler = $this->generateRequestExportAccountListHandler();
        $handler->__invoke($command);

        self::assertSame($uuid->toRfc4122(), $command->getArtifactUuid()->toRfc4122());
    }
}
