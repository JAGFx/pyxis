<?php

namespace App\Shared\Cqs\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class CorrelationStamp implements StampInterface
{
    public function __construct(
        public string $correlationId,
    ) {
    }
}
