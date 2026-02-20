<?php

declare(strict_types=1);

namespace App\Tests\Unit\Module\Exporter\Domain\Artifact\Validator;

use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Validator\ArtifactMustBePending;
use App\Module\Exporter\Domain\Artifact\Validator\ArtifactMustBePendingValidator;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ArtifactMustBePendingValidatorTest extends TestCase
{
    private ArtifactMustBePendingValidator $validator;

    private ExecutionContextInterface $context;

    private EntityFinder $entityFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFinder = $this->createMock(EntityFinder::class);
        $this->validator    = new ArtifactMustBePendingValidator($this->entityFinder);
        $this->context      = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testThrowsExceptionWithInvalidConstraint(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $invalidConstraint = $this->createMock(Constraint::class);

        $this->validator->validate('some-uuid', $invalidConstraint);
    }

    public static function provideNonStringNonNullValues(): Generator
    {
        yield 'integer value should throw exception' => [42];
        yield 'array value should throw exception' => [[]];
        yield 'object value should throw exception' => [new stdClass()];
        yield 'boolean value should throw exception' => [true];
    }

    #[DataProvider('provideNonStringNonNullValues')]
    public function testThrowsExceptionWhenValueIsNotAStringOrNull(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->entityFinder
            ->expects(self::never())
            ->method('findByUuidIdentifierOrFail');

        $this->validator->validate($value, new ArtifactMustBePending());
    }

    public function testNullValueIsPassedToEntityFinder(): void
    {
        $artifact = new Artifact('export_command');
        // artifact sans finishedAt = PENDING, ne déclenche pas de violation

        $this->entityFinder
            ->expects(self::once())
            ->method('findByUuidIdentifierOrFail')
            ->with(Artifact::class, null)
            ->willReturn($artifact);

        $this->context
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(null, new ArtifactMustBePending());
    }

    public function testNoViolationWhenArtifactIsPending(): void
    {
        $artifact = new Artifact('export_command');
        // artifact sans finishedAt = PENDING

        $this->entityFinder
            ->expects(self::once())
            ->method('findByUuidIdentifierOrFail')
            ->with(Artifact::class, 'some-uuid')
            ->willReturn($artifact);

        $this->context
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate('some-uuid', new ArtifactMustBePending());
    }

    public static function provideNonPendingArtifacts(): Generator
    {
        $done = new Artifact('export_command');
        $done->setFinishedAt(new DateTimeImmutable());
        $done->setDocumentPath('path/to/document');

        yield 'DONE artifact should trigger violation' => [$done];

        $failed = new Artifact('export_command');
        $failed->setFinishedAt(new DateTimeImmutable());

        yield 'FAILED artifact should trigger violation' => [$failed];

        $disabled = new Artifact('export_command');
        $disabled->setFinishedAt(new DateTimeImmutable());
        $disabled->setDisabledAt(new DateTimeImmutable());

        yield 'DISABLED artifact should trigger violation' => [$disabled];
    }

    #[DataProvider('provideNonPendingArtifacts')]
    public function testViolationWhenArtifactIsNotPending(Artifact $artifact): void
    {
        $uuid = 'some-uuid';

        $this->entityFinder
            ->expects(self::once())
            ->method('findByUuidIdentifierOrFail')
            ->with(Artifact::class, $uuid)
            ->willReturn($artifact);

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraintViolationBuilder->expects(self::once())->method('setInvalidValue')->with($uuid)->willReturnSelf();
        $constraintViolationBuilder->expects(self::once())->method('addViolation');

        $constraint = new ArtifactMustBePending();

        $this->context
            ->expects(self::once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($constraintViolationBuilder);

        $this->validator->validate($uuid, $constraint);
    }
}
