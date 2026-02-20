<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use ReflectionException;

/**
 * @see AttachDocumentToArtifactCommand
 */
readonly class AttachDocumentToArtifactHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityFinder $entityFinder,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(AttachDocumentToArtifactCommand $command): void
    {
        $artifact = ($command->isNested())
            ? new Artifact($command->getRequestExportCommandName())->setDocumentType($command->getDocumentType())
            : $this->entityFinder->findByUuidIdentifierOrFail(
                Artifact::class,
                $command->getParentArtifactUuid()
            );

        if (!$artifact->isPending()) {
            // TODO: Use custom validation on business group??
            throw new LogicException('Unable to attach document to this artifact: Already finished.');
        }

        $artifact
            ->setDocumentName($command->getDocumentName())
            ->setDocumentPath($command->getDocumentPath())
            ->setStorage($command->getStorage())
            ->setFinishedAt(new DateTimeImmutable())
        ;

        if ($command->isNested()) {
            $parentArtifact = $this->entityFinder->findByUuidIdentifierOrFail(
                Artifact::class,
                $command->getParentArtifactUuid()
            );
            $artifact->setParent($parentArtifact);

            $this->entityManager->persist($artifact);
        }

        $this->entityManager->flush();
    }
}
