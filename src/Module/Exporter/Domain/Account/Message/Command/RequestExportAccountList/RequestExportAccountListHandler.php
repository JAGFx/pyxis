<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @see RequestExportAccountListCommand
 */
readonly class RequestExportAccountListHandler implements CommandHandlerInterface
{
    public function __construct(
        private DocumentFactoryResolver $factoryResolver,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RequestExportAccountListCommand $command): void
    {
        $factory = $this->factoryResolver->resolve(
            $command->getTarget(),
            $command->getDocumentType()
        );

        $document = $factory->createDocument($command);

        $artifact = new Artifact($command::class) // TODO: use FQCN helper
            ->setDocumentName($document->getFileName())
            ->setDocumentPath($document->getPath())
            ->setStorage($command->getStorage())
        ;

        $this->entityManager->persist($artifact);
        $this->entityManager->flush();
    }
}
