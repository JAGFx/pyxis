<?php

namespace App\Domain\Entry\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @see HasEnoughAmountAssignmentValidator
 */
#[Attribute]
class HasEnoughAmountAssignment extends Constraint
{
    public string $message = 'entry.create_or_update.has_enough_amount_assignment';

    public function __construct(?array $groups = null)
    {
        parent::__construct(groups: $groups);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
