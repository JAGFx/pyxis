<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
        $artifact = $this->entityFinder->findByUuidIdentifierOrFail(
            Artifact::class,
            $command->getArtifactUuid()
        );

        $artifact
            ->setDocumentName($command->getDocumentName())
            ->setDocumentPath($command->getDocumentPath())
            ->setStorage($command->getStorage());

        if ($command->isAsLast()) {
            $artifact->setFinishedAt(new DateTimeImmutable());
        }

        if ($command->hasParent()) {
            $parentArtifact = $this->entityFinder->findByUuidIdentifierOrFail(
                Artifact::class,
                $command->getParentArtifactUuid()
            );
            $artifact->setParent($parentArtifact);
        }

        $this->entityManager->flush();
    }
}
