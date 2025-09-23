<?php

namespace App\Domain\Account\Message\Command\CreateOrUpdateAccount;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see CreateOrUpdateAccountHandler
 */
#[Map(Account::class)]
class CreateOrUpdateAccountCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;

    public function __construct(
        #[Assert\NotBlank]
        private string $name = '',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CreateOrUpdateAccountCommand
    {
        $this->name = $name;

        return $this;
    }
}
