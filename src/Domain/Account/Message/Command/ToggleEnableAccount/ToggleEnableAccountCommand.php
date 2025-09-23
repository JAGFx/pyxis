<?php

namespace App\Domain\Account\Message\Command\ToggleEnableAccount;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;

/**
 * @see ToggleEnableAccountHandler
 */
class ToggleEnableAccountCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
