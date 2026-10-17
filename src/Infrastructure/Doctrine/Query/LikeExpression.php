<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Query;

final class LikeExpression
{
    /**
     * Pattern matching values that contain $value literally: `%` and `_` typed by the user are not wildcards.
     * Escaped with a backslash, the default LIKE escape character of MySQL.
     */
    public static function contains(string $value): string
    {
        return '%' . addcslashes($value, '%_\\') . '%';
    }
}
