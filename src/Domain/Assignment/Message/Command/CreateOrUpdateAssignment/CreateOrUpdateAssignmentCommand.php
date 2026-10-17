<?php

namespace App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Validator\AmountLessOrEqualTotalValueAccount;
use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Infrastructure\Cqs\Validation\ValidationGroupEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * TODO:
 *  - Add resolver on ObjectMapper to map id to entity
 *
 * @see CreateOrUpdateAssignmentHandler
 */
#[Map(Assignment::class)]
#[AmountLessOrEqualTotalValueAccount(groups: [ValidationGroupEnum::Business->value])]
class CreateOrUpdateAssignmentCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;

    public function __construct(
        #[NotBlank]
        #[Length(max: 255)]
        private string $name = '',

        // Starting amount: 0 for an envelope filled over time
        #[PositiveOrZero]
        private int $amount = 0,

        #[NotNull]
        private ?Account $account = null,

        #[Positive]
        private ?int $target = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateAssignmentCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): CreateOrUpdateAssignmentCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTarget(): ?int
    {
        return $this->target;
    }

    public function setTarget(?int $target): CreateOrUpdateAssignmentCommand
    {
        $this->target = $target;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): CreateOrUpdateAssignmentCommand
    {
        $this->account = $account;

        return $this;
    }
}
