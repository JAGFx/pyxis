<?php

namespace App\Module\Export\Domain\Message\Query\ExportAccountData;

use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see ExportAccountDataQuery
 */
readonly class ExportAccountDataHandler implements QueryHandlerInterface
{
    /**
     * @return string[]
     */
    public function __invoke(ExportAccountDataQuery $query): array
    {
        // Implementation for exporting account data goes here.
        return [
            'OK Google',
        ];
    }
}
