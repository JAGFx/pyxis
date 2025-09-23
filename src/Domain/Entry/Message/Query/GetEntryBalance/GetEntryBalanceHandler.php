<?php

namespace App\Domain\Entry\Message\Query\GetEntryBalance;

use App\Domain\Entry\Repository\EntryRepository;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use App\Shared\Utils\Statistics;

/**
 * @see GetEntryBalanceQuery
 */
readonly class GetEntryBalanceHandler implements QueryHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
    ) {
    }

    public function __invoke(GetEntryBalanceQuery $query): EntryBalance
    {
        /** @var array<string, mixed> $data */
        $data = $this->repository
            ->balance($query)
            ->getQuery()
            ->getResult();

        $spentAmount    = Statistics::filterBy($data, 'id', null);
        $forecastAmount = Statistics::filterBy($data, 'id', null, true);

        $spentAmount    = Statistics::sumOf($spentAmount, 'sum');
        $forecastAmount = Statistics::sumOf($forecastAmount, 'sum');

        return new EntryBalance($spentAmount, $forecastAmount);
    }
}
