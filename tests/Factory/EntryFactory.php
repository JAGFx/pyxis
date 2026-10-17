<?php

namespace App\Tests\Factory;

use App\Domain\Entry\Entity\Entry;
use DateTimeImmutable;
use Zenstruck\Foundry\ModelFactory;

final class EntryFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'amount'    => self::faker()->numberBetween(-1000000, 1000000),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name'      => self::faker()->text(),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected static function getClass(): string
    {
        return Entry::class;
    }
}
