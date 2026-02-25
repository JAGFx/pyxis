<?php

namespace App\Module\Exporter\Domain\Artifact\Repository;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Infrastructure\Document\Model\Document;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<Artifact>
 */
class ArtifactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artifact::class);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getArtifactsQueryBuilder(FindArtifactsQuery $query, ?DateTimeImmutable $initialDate = null): QueryBuilder
    {
        $initialDate ??= new DateTimeImmutable();

        $queryBuilder = $this->createQueryBuilder('a');

        if (!is_null($query->getMaxAgeInMinutes())) {
            $ageDate = $initialDate->modify(sprintf('- %d minutes', $query->getMaxAgeInMinutes()));

            $queryBuilder
                ->andWhere('a.createdAt < :createdAt')
                ->setParameter('createdAt', $ageDate)
            ;
        }

        switch ($query->getStatus()) {
            case ArtifactStatusEnum::DISABLED:
                $queryBuilder->andWhere('a.finishedAt IS NOT NULL AND a.disabledAt IS NOT NULL');
                break;
            case ArtifactStatusEnum::FAILED:
                $queryBuilder->andWhere('a.finishedAt IS NOT NULL AND a.disabledAt IS NULL AND a.documentPath IS NULL');
                break;
            case ArtifactStatusEnum::DONE:
                $queryBuilder->andWhere('a.finishedAt IS NOT NULL AND a.disabledAt IS NULL AND a.documentPath IS NOT NULL');
                break;
            case ArtifactStatusEnum::PENDING:
                $queryBuilder->andWhere('a.finishedAt IS NULL');
                break;
        }

        if ('createdAt' === $query->getOrderBy()) {
            $queryBuilder->orderBy('a.createdAt', $query->getOrderDirection()->value);
        }

        return $queryBuilder;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function forceFinishPendingArtifacts(FindArtifactsQuery $query): void
    {
        $this->getArtifactsQueryBuilder($query)
            ->update()
            ->set('a.finishedAt', ':finishedAt')
            ->setParameter('finishedAt', new DateTimeImmutable())
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return DocumentInterface[]
     *
     * @throws DateMalformedStringException
     */
    public function getOldestArtifactDocuments(FindArtifactsQuery $query): array
    {
        /** @var DocumentInterface[] $documents */
        $documents = $this
            ->getArtifactsQueryBuilder($query)
            ->select(sprintf('NEW %s(a.documentPath, a.documentName, a.documentType, a.storage, a.documentPath)', Document::class))
            ->getQuery()
            ->getResult()
        ;

        return $documents;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function disableOldestArtifacts(FindArtifactsQuery $query): void
    {
        if (ArtifactStatusEnum::DONE !== $query->getStatus()) {
            throw new LogicException('Only done artifacts can be disabled');
        }

        $this->getArtifactsQueryBuilder($query)
            ->update()
            ->set('a.disabledAt', ':disabledAt')
            ->setParameter('disabledAt', new DateTimeImmutable())
            ->getQuery()
            ->execute()
        ;
    }
}
