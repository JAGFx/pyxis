<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Factory\CsvListAccountDocumentFactory;
use App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact\AttachDocumentToArtifactCommand;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Shared\Message\Command\ArtifactRequestExportHandlerTrait;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * @see RequestExportAccountListCommand
 */
readonly class RequestExportAccountListHandler implements CommandHandlerInterface
{
    use ArtifactRequestExportHandlerTrait;

    public function __construct(
        private DocumentFactoryResolver $factoryResolver,
        private MessageBus $messageBus,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws CannotInsertRecord
     * @throws Throwable
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function __invoke(RequestExportAccountListCommand $command): void
    {
        // Step 1: Create an artifact if not already done. Its redispatched after.
        if (!$command->isOnExportingStage()) {
            $this->createEmptyArtifact($command);

            return;
        }

        // Step 2: Generate a document + store it
        /** @var CsvListAccountDocumentFactory $factory */
        $factory = $this->factoryResolver->resolve(
            $command->getTarget(),
            $command->getDocumentType()
        );
        $document = $factory->createDocument($command);

        // Step 3: Attach a document to artifact + mark it as finished
        /** @var Uuid $artifactUuid */
        $artifactUuid                    = $command->getArtifactUuid();
        $attachDocumentToArtifactCommand = new AttachDocumentToArtifactCommand(
            $artifactUuid->toRfc4122(),
            $document->getFileName(),
            $document->getPath(),
            $command->getStorage(),
            true
        );
        $this->messageBus->dispatch($attachDocumentToArtifactCommand);

        // Step 4: Notify user
        $email = new TemplatedEmail()
            ->from('Pyxis <noreplay@me.com>')
            ->to('email@me.com')
            ->subject("Votre demande d'export est prête")
            ->context([
                'exportName'    => $command->getTranslationKey(),
                'artifactUuids' => [
                    $artifactUuid->toRfc4122(),
                ],
            ])
            ->htmlTemplate('module/exporter/domain/artifact/email/artifact_attached_to_export_request.html.twig');

        $this->mailer->send($email);

        // TODO: Add try-catch to remove file in storage if an error occurred on attach document
    }
}
