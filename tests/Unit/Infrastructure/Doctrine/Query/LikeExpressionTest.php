<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Doctrine\Query;

use App\Infrastructure\Doctrine\Query\LikeExpression;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LikeExpressionTest extends TestCase
{
    public static function valuesDataset(): Generator
    {
        yield 'Plain text' => ['Courses', '%Courses%'];
        yield 'Percent' => ['50%', '%50\%%'];
        yield 'Underscore' => ['a_b', '%a\_b%'];
        yield 'Backslash' => ['a\b', '%a\\\b%'];
    }

    #[DataProvider('valuesDataset')]
    public function testContainsEscapesWildcards(string $value, string $expected): void
    {
        self::assertSame($expected, LikeExpression::contains($value));
    }
}
