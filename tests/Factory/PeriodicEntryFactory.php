<?php

namespace App\Tests\Factory;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use DateTimeImmutable;
use Zenstruck\Foundry\ModelFactory;

final class PeriodicEntryFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'amount'        => self::faker()->numberBetween(1, 1000000),
            'createdAt'     => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'executionDate' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name'          => self::faker()->text(),
            'updatedAt'     => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected static function getClass(): string
    {
        return PeriodicEntry::class;
    }
}
