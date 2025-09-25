<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Infrastructure\Cqs\Message\Command\HasOriginIntIdentifierTrait;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see RemoveEntryHandler
 */
class RemoveEntryCommand implements CommandInterface
{
    use HasOriginIntIdentifierTrait;
}
