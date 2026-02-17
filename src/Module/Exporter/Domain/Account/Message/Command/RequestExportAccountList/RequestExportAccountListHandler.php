<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Factory\CsvListAccountDocumentFactory;
use App\Module\Exporter\Domain\Artifact\Mailer\ArtifactMailerDispatcher;
use App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact\AttachDocumentToArtifactCommand;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use App\Module\Exporter\Shared\Cqs\Message\Command\ArtifactRequestExportHandlerTrait;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
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
        private EntityManagerInterface $entityManager,
        private ArtifactMailerDispatcher $mailerDispatcher,
        private StorageSystem $storageSystem,
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
        // Step 1: Create an artifact if not already done. This command will be redispatched after.
        if (!$command->isOnExportingStage()) {
            $this->createParentEmptyArtifact($command);

            return;
        }

        // Step 2: Generate a document + store it
        /** @var CsvListAccountDocumentFactory $factory */
        $factory = $this->factoryResolver->resolve(
            $command->getTarget(),
            $command->getDocumentType()
        );
        $document = $factory->createDocument($command);

        // Step 3: Attach a document to an artifact
        try {
            $parentArtifactUuid              = $command->getParentArtifactUuid();
            $attachDocumentToArtifactCommand = new AttachDocumentToArtifactCommand(
                $parentArtifactUuid->toRfc4122(),
                $command->getTranslationKey(),
                $document->getFileName(),
                $document->getPath(),
                $command->getStorage()
            );
            $this->messageBus->dispatch($attachDocumentToArtifactCommand);
        } catch (Throwable $throwable) {
            $this->storageSystem->delete($document);

            throw $throwable;
        }

        // Step 4: Notify user
        $this->mailerDispatcher->requestExportFinished($command);
    }
}
