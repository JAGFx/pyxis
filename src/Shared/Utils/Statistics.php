<?php

namespace App\Shared\Utils;

class Statistics
{
    /**
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    public static function filterBy(array $statistics, string $property, mixed $value, bool $negate = false): array
    {
        return array_filter(
            $statistics,
            static fn (array $statistic): bool => ($negate) // @phpstan-ignore-line
                ? $statistic[$property] !== $value
                : $statistic[$property] === $value
        );
    }

    /**
     * @param array<string, mixed> $statistics
     */
    public static function sumOf(array $statistics, string $property): int
    {
        return array_reduce(
            $statistics,
            static function (int $currentSum, array $item) use ($property): int {
                /** @var array<int|string|null> $currentItem */
                $currentItem = $item;

                return $currentSum + (int) ($currentItem[$property] ?? 0);
            },
            0
        );
    }
}
