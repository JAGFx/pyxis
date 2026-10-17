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
    public string $message = 'Ce montant doit être inférieur ou égal au montant encore disponible sur le compte ({{ total }}€)';

    public string $accountLockedMessage = 'Le compte ne peut pas changer : des entrées sont liées à cette affectation.';

    public function __construct(?array $groups = null)
    {
        parent::__construct(groups: $groups);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
