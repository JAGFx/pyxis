<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class DocumentFactoryResolver
{
    public const string FACTORY_TAG = 'app.module.document_factory';

    public function __construct(
        /** @var iterable<DocumentFactoryInterface> */
        #[TaggedIterator(self::FACTORY_TAG)]
        private readonly iterable $factories,
    ) {
    }

    public function resolve(string $targetClass, DocumentTypeEnum $documentType): DocumentFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->support($targetClass, $documentType)) {
                return $factory;
            }
        }

        throw new RuntimeException(sprintf('No factory found for target "%s" and type "%s"', $targetClass, $documentType->value));
    }
}
