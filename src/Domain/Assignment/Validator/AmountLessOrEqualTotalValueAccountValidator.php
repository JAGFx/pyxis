<?php

namespace App\Domain\Assignment\Validator;

use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignmentCommand;
use App\Shared\Operator\EntryOperator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AmountLessOrEqualTotalValueAccountValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntryOperator $entryOperator,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AmountLessOrEqualTotalValueAccount) {
            throw new InvalidArgumentException(sprintf('Expected instance of %s, got %s', AmountLessOrEqualTotalValueAccount::class, get_debug_type($constraint)));
        }

        if (!$value instanceof CreateOrUpdateAssignmentCommand) {
            return;
        }

        $amountBalance = $this->entryOperator->getAmountBalance($value->getAccount());

        // TODO: Add test for it
        if ($value->getAmount() > $amountBalance->getTotal()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ total }}', number_format($amountBalance->getTotal(), 2, ',', ' '))
                ->setInvalidValue($value->getAmount())
                ->atPath('amount')
                ->addViolation();
        }
    }
}
