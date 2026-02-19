<?php

namespace App\Module\Exporter\Shared\Cqs\Message\Command;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Uid\Uuid;
use Throwable;

trait ArtifactRequestExportHandlerTrait
{
    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function createParentEmptyArtifact(RequestExportCommandInterface $command): void
    {
        $artifactUuid = Uuid::v7();
        $artifact     = new Artifact(
            $command->getTranslationKey(),
            $artifactUuid,
        )->setDocumentType($command->getDocumentType());

        $this->entityManager->persist($artifact);
        $this->entityManager->flush();

        $command->setParentArtifactUuid($artifactUuid);

        $this->messageBus->dispatch($command, [
            new DispatchAfterCurrentBusStamp(),
        ]);
    }
}
