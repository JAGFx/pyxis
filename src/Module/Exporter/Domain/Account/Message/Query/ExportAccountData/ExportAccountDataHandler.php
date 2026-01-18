<?php

namespace App\Module\Exporter\Domain\Account\Message\Query\ExportAccountData;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see ExportAccountDataQuery
 */
readonly class ExportAccountDataHandler implements QueryHandlerInterface
{
    public function __construct(
        private DocumentFactoryResolver $factoryResolver,
    ) {
    }

    public function __invoke(ExportAccountDataQuery $query): DocumentInterface
    {
        $factory = $this->factoryResolver->resolve(
            $query->getTarget(),
            $query->getDocumentType()
        );

        return $factory->createDocument($query);
    }
}
