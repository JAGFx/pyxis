<?php

namespace App\Domain\PeriodicEntry\Exception;

use App\Infrastructure\Logging\ExpectedFailureInterface;
use Exception;

/**
 * The periodic entry is skipped: not scheduled today, already executed this month or disabled.
 */
class PeriodicEntrySplitBudgetException extends Exception implements ExpectedFailureInterface
{
}
