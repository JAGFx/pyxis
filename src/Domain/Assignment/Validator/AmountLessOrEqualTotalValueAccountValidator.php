<?php

namespace App\Domain\Assignment\Validator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment\CreateOrUpdateAssignmentCommand;
use App\Infrastructure\Doctrine\Service\EntityFinder;
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
        private readonly EntityFinder $entityFinder,
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

        $entity = $this->entityFinder->findByIntIdentifier(
            Account::class,
            $value->getAccountId()
        );

        if (null === $entity) {
            return;
        }

        $amountBalance = $this->entryOperator->getAmountBalance($entity);

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
