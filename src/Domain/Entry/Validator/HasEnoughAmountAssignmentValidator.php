<?php

namespace App\Domain\Entry\Validator;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @see HasEnoughAmountAssignment
 */
class HasEnoughAmountAssignmentValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityFinder $entityFinder,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasEnoughAmountAssignment) {
            throw new UnexpectedTypeException($constraint, HasEnoughAmountAssignment::class);
        }

        if (!$value instanceof CreateOrUpdateEntryCommand) {
            throw new UnexpectedTypeException($value, CreateOrUpdateEntryCommand::class);
        }

        $assignment = $value->getAssignment();

        if (is_null($assignment)) {
            return;
        }

        // On update, the balance already holds the stored amount of the entry: replace it by the new one
        $storedAmount = 0;
        if (!is_null($value->getOriginId())) {
            $storedEntry = $this->entityFinder->findByIntIdentifier(Entry::class, $value->getOriginId());

            if ($storedEntry instanceof Entry && $storedEntry->getAssignment() === $assignment) {
                $storedAmount = $storedEntry->getAmount();
            }
        }

        $balance    = $assignment->getBalance() - $storedAmount;
        $restAmount = $balance + $value->getAmount();

        if ($restAmount < 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%assignmentAmount%', number_format($balance / 100, 2, ',', ' '))
                ->setInvalidValue($value->getAmount())
                ->atPath('amount')
                ->addViolation();
        }
    }
}
