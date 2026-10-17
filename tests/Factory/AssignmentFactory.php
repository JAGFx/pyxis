<?php

namespace App\Tests\Factory;

use App\Domain\Assignment\Entity\Assignment;
use Zenstruck\Foundry\ModelFactory;

class AssignmentFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Assignment::class;
    }

    protected function getDefaults(): array
    {
        return [
            'name'    => self::faker()->words(3, true),
            'amount'  => self::faker()->numberBetween(1000, 100000),
            'account' => AccountFactory::new(),
        ];
    }
}
