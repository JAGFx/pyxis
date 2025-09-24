<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Command\HasOriginIntIdentifierTrait;

/**
 * @see RemoveEntryHandler
 */
class RemoveEntryCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
