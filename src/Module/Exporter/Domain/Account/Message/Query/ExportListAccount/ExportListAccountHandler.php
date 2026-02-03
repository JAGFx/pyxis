<?php

namespace App\Module\Exporter\Domain\Account\Message\Query\ExportListAccount;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryResolver;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentInterface;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see ExportListAccountQuery
 */
readonly class ExportListAccountHandler implements QueryHandlerInterface
{
    public function __construct(
        private DocumentFactoryResolver $factoryResolver,
    ) {
    }

    public function __invoke(ExportListAccountQuery $query): DocumentInterface
    {
        $factory = $this->factoryResolver->resolve(
            $query->getTarget(),
            $query->getDocumentType()
        );

        return $factory->createDocument($query);
    }
}
