<?php

namespace App\Tests\Factory;

use App\Domain\Budget\Entity\HistoryBudget;
use DateTimeImmutable;
use Zenstruck\Foundry\ModelFactory;

final class HistoryBudgetFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return HistoryBudget::class;
    }

    protected function getDefaults(): array
    {
        return [
            'date'             => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'relativeProgress' => self::faker()->randomFloat(2, 0, 1),
            'spent'            => self::faker()->numberBetween(0, 100000),
            'amount'           => self::faker()->numberBetween(10000, 200000),
            'budget'           => BudgetFactory::new(),
        ];
    }
}
