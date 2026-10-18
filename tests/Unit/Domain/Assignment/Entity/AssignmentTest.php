<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Assignment\Entity;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Entity\Entry;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AssignmentTest extends TestCase
{
    public static function targetProgressDataset(): Generator
    {
        yield 'Without target' => [0, 30000, null, null];
        yield 'In progress' => [0, 30000, 200000, 15.0];
        yield 'Starting amount counted' => [50000, 50000, 200000, 50.0];
        yield 'Target exceeded' => [0, 250000, 200000, 125.0];
    }

    #[DataProvider('targetProgressDataset')]
    public function testTargetProgress(int $startingAmount, int $linkedAmount, ?int $target, ?float $expected): void
    {
        $assignment = new Assignment()->setAmount($startingAmount)->setTarget($target);
        $assignment->getEntries()->add(new Entry()->setAmount($linkedAmount)->setAssignment($assignment));

        self::assertSame($expected, $assignment->getTargetProgress());
    }
}
