<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

/**
 * An exception thrown on purpose to skip a message (not scheduled, already done...): logged as info, not as an error.
 */
interface ExpectedFailureInterface
{
}
