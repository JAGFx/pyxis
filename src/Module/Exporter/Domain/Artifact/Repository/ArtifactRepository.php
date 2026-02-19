<?php

namespace App\Module\Exporter\Domain\Artifact\Repository;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
            case ArtifactStatusEnum::PENDING:
                $queryBuilder->andWhere('a.finishedAt IS NULL');
                break;
            case ArtifactStatusEnum::DONE:
                $queryBuilder->andWhere('a.finishedAt IS NOT NULL');
                break;
        }

        return $queryBuilder;
    }
}
