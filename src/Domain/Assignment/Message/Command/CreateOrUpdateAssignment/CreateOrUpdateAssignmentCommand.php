<?php

namespace App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Validator\AmountLessOrEqualTotalValueAccount;
use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\ObjectMapper\EntityIdTransformer;
use App\Shared\Validation\ValidationGroupEnum;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

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
        private string $name = '',

        #[NotBlank]
        #[Positive]
        private float $amount = 0.0,

        #[NotNull]
        #[Map(target: 'account', transform: EntityIdTransformer::class)]
        private ?int $accountId = null,
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): CreateOrUpdateAssignmentCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): CreateOrUpdateAssignmentCommand
    {
        $this->accountId = $accountId;

        return $this;
    }
}
