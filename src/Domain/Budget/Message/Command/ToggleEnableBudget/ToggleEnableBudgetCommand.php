<?php

namespace App\Domain\Budget\Message\Command\ToggleEnableBudget;

use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see ToggleEnableBudgetHandler
 */
class ToggleEnableBudgetCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
