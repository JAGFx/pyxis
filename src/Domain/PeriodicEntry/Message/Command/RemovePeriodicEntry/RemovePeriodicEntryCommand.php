<?php

namespace App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;

/**
 * @see RemovePeriodicEntryHandler
 */
class RemovePeriodicEntryCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
