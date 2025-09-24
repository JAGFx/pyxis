<?php

namespace App\Infrastructure\Doctrine\Exception;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(string $className, ?int $id)
    {
        parent::__construct(sprintf('%s not found with id %s', $className, $id));
    }
}
