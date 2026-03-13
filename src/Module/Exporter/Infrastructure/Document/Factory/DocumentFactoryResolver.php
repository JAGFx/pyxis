<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class DocumentFactoryResolver
{
    public const string FACTORY_TAG = 'app.module.document_factory';

    public function __construct(
        /** @var iterable<DocumentFactoryInterface> */
        #[TaggedIterator(self::FACTORY_TAG)]
        private iterable $factories,
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function resolve(RequestExportCommandInterface $command): DocumentFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->support($command)) {
                return $factory;
            }
        }

        throw new RuntimeException(sprintf('No factory found for target "%s" and type "%s"', $command::class, $command->getDocumentType()->value));
    }
}
