<?php

namespace App\Domain\Budget\Message\Command\ToggleEnableBudget;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;

/**
 * @see ToggleEnableBudgetHandler
 */
class ToggleEnableBudgetCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
