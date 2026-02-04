<?php

namespace App\Infrastructure\Cqs\Message\Command;

use ReflectionClass;

trait TranslatableTrait
{
    public function getTranslationKey(): string
    {
        $commandName = new ReflectionClass($this)->getShortName();

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $commandName)); // @phpstan-ignore-line
    }
}
