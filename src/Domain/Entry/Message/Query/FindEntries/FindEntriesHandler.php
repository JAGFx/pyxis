<?php

namespace App\Domain\Entry\Message\Query\FindEntries;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @see FindEntriesQuery
 */
readonly class FindEntriesHandler implements QueryHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
        private PaginatorInterface $paginator,
    ) {
    }

    /**
     * @return PaginationInterface<int, Entry>
     */
    public function __invoke(FindEntriesQuery $query): PaginationInterface
    {
        /** @var PaginationInterface<int, Entry> $pagination */
        $pagination = $this->paginator->paginate(
            $this->repository->getEntriesQueryBuilder($query),
            $query->getPage(),
            $query->getPageSize()
        );

        return $pagination;
    }
}
