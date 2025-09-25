<?php

namespace App\Domain\Account\Message\Command\ToggleEnableAccount;

use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see ToggleEnableAccountHandler
 */
class ToggleEnableAccountCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
