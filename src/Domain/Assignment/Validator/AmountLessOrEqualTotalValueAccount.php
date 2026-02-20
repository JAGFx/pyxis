<?php

namespace App\Domain\Assignment\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @see AmountLessOrEqualTotalValueAccountValidator
 */
#[Attribute]
class AmountLessOrEqualTotalValueAccount extends Constraint
{
    public string $message = 'assignment.create_or_update.has_enough_amount';

    public function __construct(?array $groups = null)
    {
        parent::__construct(groups: $groups);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
