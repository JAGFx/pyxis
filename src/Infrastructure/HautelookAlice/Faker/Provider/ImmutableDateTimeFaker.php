<?php

namespace App\Infrastructure\HautelookAlice\Faker\Provider;

use DateTimeImmutable;
use Exception;
use Faker\Provider\Base;
use Faker\Provider\DateTime;

final class ImmutableDateTimeFaker extends Base
{
    public static function immutableDateTimeBetween(string $startDate = '-30 years', string $endDate = 'now'): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable(
            DateTime::dateTimeBetween($startDate, $endDate)
        );
    }

    /**
     * @throws Exception
     */
    public static function dateTimeImmutable(string $dateTime): DateTimeImmutable
    {
        return new DateTimeImmutable($dateTime);
    }
}
