<?php

namespace App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry;

use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see RemovePeriodicEntryHandler
 */
class RemovePeriodicEntryCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
