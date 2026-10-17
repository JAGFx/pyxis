<?php

namespace App\Domain\Assignment\Validator;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment\CreateOrUpdateAssignmentCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Message\Query\GetAmountBalance\GetAmountBalanceQuery;
use App\Shared\ValueObject\AmountBalance;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Throwable;

class AmountLessOrEqualTotalValueAccountValidator extends ConstraintValidator
{
    public function __construct(
        private readonly MessageBus $messageBus,
        private readonly EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AmountLessOrEqualTotalValueAccount) {
            throw new InvalidArgumentException(sprintf('Expected instance of %s, got %s', AmountLessOrEqualTotalValueAccount::class, get_debug_type($constraint)));
        }

        if (!$value instanceof CreateOrUpdateAssignmentCommand) {
            return;
        }

        $account = $value->getAccount();

        // Reported by the NotNull constraint
        if (!$account instanceof Account) {
            return;
        }

        /** @var AmountBalance[] $amountBalances */
        $amountBalances = $this->messageBus->dispatch(new GetAmountBalanceQuery([$account->getId()]));
        /** @var AmountBalance $amountBalance */
        $amountBalance = reset($amountBalances);

        // The other assignments of the account already hold part of its total
        $otherAssignments = $amountBalance->getAssignments();

        if (!is_null($value->getOriginId())) {
            $currentAssignment = $this->entityFinder->findByIntIdentifier(Assignment::class, $value->getOriginId());

            if ($currentAssignment instanceof Assignment && $currentAssignment->getAccount()->getId() === $account->getId()) {
                $otherAssignments -= $currentAssignment->getAmount();
            }
        }

        $availableAmount = round($amountBalance->getTotal() - $otherAssignments, 2);

        if (round($value->getAmount(), 2) > $availableAmount) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ total }}', number_format($availableAmount, 2, ',', ' '))
                ->setInvalidValue($value->getAmount())
                ->atPath('amount')
                ->addViolation();
        }
    }
}
