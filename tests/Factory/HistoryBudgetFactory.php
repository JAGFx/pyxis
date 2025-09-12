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
            'spent'            => self::faker()->randomFloat(2, 0, 1000),
            'amount'           => self::faker()->randomFloat(2, 100, 2000),
            'budget'           => BudgetFactory::new(),
        ];
    }
}
