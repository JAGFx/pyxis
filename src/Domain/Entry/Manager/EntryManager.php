<?php

namespace App\Domain\Entry\Manager;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\EntryCreateOrUpdateCommand;
use App\Domain\Entry\Message\Command\EntryRemoveCommand;
use App\Domain\Entry\Message\Query\EntrySearchQuery;
use App\Domain\Entry\Repository\EntryRepository;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Shared\Utils\Statistics;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class EntryManager
{
    public function __construct(
        private EntryRepository $repository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    // TODO: Create dedicated query
    public function balance(?EntrySearchQuery $searchQuery = null): EntryBalance
    {
        /** @var array<string, mixed> $data */
        $data = $this->repository
            ->balance($searchQuery ?? new EntrySearchQuery())
            ->getQuery()
            ->getResult();

        $spentAmount    = Statistics::filterBy($data, 'id', null);
        $forecastAmount = Statistics::filterBy($data, 'id', null, true);

        $spentAmount    = Statistics::sumOf($spentAmount, 'sum');
        $forecastAmount = Statistics::sumOf($forecastAmount, 'sum');

        return new EntryBalance($spentAmount, $forecastAmount);
    }

    public function create(EntryCreateOrUpdateCommand $command, bool $flush = true): void
    {
        /** @var Entry $entry */
        $entry = $this->objectMapper->map($command, Entry::class);

        $this->repository->create($entry);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(EntryCreateOrUpdateCommand $command, bool $flush = true): void
    {
        /** @var Entry $entry */
        $entry = $this->objectMapper->map($command, $command->getOrigin());

        if ($entry->isEditable()) {
            return; // TODO: Throw exception instead
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(EntryRemoveCommand $command, bool $flush = true): void
    {
        $entry = $command->getEntry();

        if ($entry->isEditable()) {
            return;
        }

        $this->repository->remove($entry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @return PaginationInterface<int, Entry>
     */
    public function getPaginated(?EntrySearchQuery $searchQuery = null): PaginationInterface
    {
        $searchQuery ??= new EntrySearchQuery();

        /** @var PaginationInterface<int, Entry> $pagination */
        $pagination = $this->paginator->paginate(
            $this->repository->getEntriesQueryBuilder($searchQuery),
            $searchQuery->getPage(),
            $searchQuery->getPageSize()
        );

        return $pagination;
    }
}
