<?php

namespace App\Domain\Account\Message\Command;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;

class ToggleEnableAccountCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
