<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Module\Exporter\Domain\Account\Factory\CsvListAccountDocumentFactory;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use DateTimeImmutable;
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
    public function __construct(
        private DocumentFactoryResolver $factoryResolver,
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
        /** @var CsvListAccountDocumentFactory $factory */
        $factory = $this->factoryResolver->resolve(
            $command->getTarget(),
            $command->getDocumentType()
        );

        $document = $factory->createDocument($command);

        $artifact = new Artifact($command->getTranslationKey())
            ->setDocumentName($document->getFileName())
            ->setDocumentPath($document->getPath())
            ->setStorage($command->getStorage())
            ->setFinishedAt(new DateTimeImmutable())
        ;

        $this->entityManager->persist($artifact);
        $this->entityManager->flush();
    }
}
