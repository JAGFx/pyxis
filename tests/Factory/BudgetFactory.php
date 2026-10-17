<?php

namespace App\Tests\Factory;

use App\Domain\Budget\Entity\Budget;
use DateTimeImmutable;
use Zenstruck\Foundry\ModelFactory;

final class BudgetFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'amount'    => self::faker()->numberBetween(1, 1000000),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name'      => self::faker()->text(),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected static function getClass(): string
    {
        return Budget::class;
    }
}
