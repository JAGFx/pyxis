<?php

namespace App\Domain\Entry\Manager;

use App\Domain\Entry\DTO\EntrySearchCommand;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Shared\Utils\Statistics;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class EntryManager
{
    public function __construct(
        private EntryRepository $repository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function balance(?EntrySearchCommand $command = null): EntryBalance
    {
        /** @var array<string, mixed> $data */
        $data = $this->repository
            ->balance($command ?? new EntrySearchCommand())
            ->getQuery()
            ->getResult();

        $spentAmount    = Statistics::filterBy($data, 'id', null);
        $forecastAmount = Statistics::filterBy($data, 'id', null, true);

        $spentAmount    = Statistics::sumOf($spentAmount, 'sum');
        $forecastAmount = Statistics::sumOf($forecastAmount, 'sum');

        return new EntryBalance($spentAmount, $forecastAmount);
    }

    public function create(Entry $entity, bool $flush = true): void
    {
        $this->repository->create($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(Entry $entry, bool $flush = true): void
    {
        if ($entry->isABalancing()) {
            return;
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Entry $entry, bool $flush = true): void
    {
        if ($entry->isABalancing()) {
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
    public function getPaginated(?EntrySearchCommand $command = null): PaginationInterface
    {
        $command ??= new EntrySearchCommand();

        /** @var PaginationInterface<int, Entry> $pagination */
        $pagination = $this->paginator->paginate(
            $this->repository->getEntriesQueryBuilder($command),
            $command->getPage(),
            $command->getPageSize()
        );

        return $pagination;
    }
}
