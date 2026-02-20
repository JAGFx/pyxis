<?php

namespace App\Module\Exporter\Domain\Artifact\Validator;

use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ArtifactMustBePendingValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ArtifactMustBePending) {
            throw new InvalidArgumentException(sprintf('Expected instance of %s, got %s', ArtifactMustBePending::class, get_debug_type($constraint)));
        }

        if (!is_string($value) && !is_null($value)) {
            throw new InvalidArgumentException(sprintf('Expected value to be a string or null, got %s', get_debug_type($value)));
        }

        $artifact = $this->entityFinder->findByUuidIdentifierOrFail(
            Artifact::class,
            $value
        );

        if ($artifact->isPending()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setInvalidValue($value)
            ->addViolation();
    }
}
