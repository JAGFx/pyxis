<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Artifact\Repository;

use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use DateMalformedStringException;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use PHPUnit\Framework\TestCase;

class ArtifactRepositoryTest extends TestCase
{
    private function generateArtifactRepository(): ArtifactRepository
    {
        return new ArtifactRepository(
            $this->createMock(ManagerRegistry::class)
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testDisableOldestArtifactsWithInvalidStatusMustThrowAnException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Only done artifacts can be disabled');

        $this->generateArtifactRepository()->disableOldestArtifacts(new FindArtifactsQuery());
    }
}
