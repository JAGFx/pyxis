<?php

namespace App\Domain\Entry\Validator;

use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @see HasEnoughAmountAssignment
 */
class HasEnoughAmountAssignmentValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasEnoughAmountAssignment) {
            throw new UnexpectedTypeException($constraint, HasEnoughAmountAssignment::class);
        }

        if (!$value instanceof CreateOrUpdateEntryCommand) {
            throw new UnexpectedTypeException($value, CreateOrUpdateEntryCommand::class);
        }

        if (!is_null($value->getOriginId()) || is_null($value->getAssignment())) {
            return;
        }

        $assignment = $value->getAssignment();
        $amount     = $value->getAmount();
        $restAmount = $assignment->getAmount() + $amount; // Remove absolute value. Aka assigmentAmount - abs(entryAmount)

        if ($restAmount < 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%assignmentAmount%', (string) $assignment->getAmount())
                ->setInvalidValue($amount)
                ->atPath('amount')
                ->addViolation();
        }
    }
}
