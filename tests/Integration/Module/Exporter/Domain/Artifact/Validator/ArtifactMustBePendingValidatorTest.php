<?php

declare(strict_types=1);

namespace App\Tests\Integration\Module\Exporter\Domain\Artifact\Validator;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Validator\ArtifactMustBePending;
use App\Tests\Factory\ArtifactFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArtifactMustBePendingValidatorTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testNoViolationWhenArtifactIsPending(): void
    {
        $uuid = Uuid::v7();

        /* @var Artifact $artifact */
        ArtifactFactory::new()->create([
            'uuid'       => $uuid,
            'finishedAt' => null,
            'disabledAt' => null,
        ]);

        $violations = $this->validator->validate($uuid->toRfc4122(), new ArtifactMustBePending());
        self::assertCount(0, $violations);
    }
}
