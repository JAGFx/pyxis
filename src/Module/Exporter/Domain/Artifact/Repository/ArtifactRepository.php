<?php

namespace App\Module\Exporter\Domain\Artifact\Repository;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
