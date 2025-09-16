<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entry\Entity;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    private function createEntry(array $flags = []): Entry
    {
        return new Entry()->setFlags($flags);
    }

    public function testIsEditableWithEmptyFlags(): void
    {
        $entry = $this->createEntry([]);

        self::assertTrue($entry->isEditable(), 'Entry with empty flags should be editable');
    }

    public function testIsEditableWithNullFlags(): void
    {
        $entry = $this->createEntry();

        self::assertTrue($entry->isEditable(), 'Entry with null flags should be editable');
    }

    public static function nonEditableFlagsProvider(): Generator
    {
        yield 'BALANCE flag' => [EntryFlagEnum::BALANCE];
        yield 'TRANSFERT flag' => [EntryFlagEnum::TRANSFERT];
        yield 'PERIODIC_ENTRY flag' => [EntryFlagEnum::PERIODIC_ENTRY];
        yield 'HIDDEN flag' => [EntryFlagEnum::HIDDEN];
    }

    #[DataProvider('nonEditableFlagsProvider')]
    public function testIsNotEditableWithSingleNonEditableFlag(EntryFlagEnum $flag): void
    {
        $entry = $this->createEntry([$flag]);

        self::assertFalse(
            $entry->isEditable(),
            sprintf('Entry with %s flag should not be editable', $flag->value)
        );
    }

    public static function multipleNonEditableFlagsProvider(): Generator
    {
        yield 'BALANCE and TRANSFERT' => [
            [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT],
        ];
        yield 'BALANCE and HIDDEN' => [
            [EntryFlagEnum::BALANCE, EntryFlagEnum::HIDDEN],
        ];
        yield 'TRANSFERT and PERIODIC_ENTRY' => [
            [EntryFlagEnum::TRANSFERT, EntryFlagEnum::PERIODIC_ENTRY],
        ];
        yield 'BALANCE, TRANSFERT and HIDDEN' => [
            [EntryFlagEnum::BALANCE, EntryFlagEnum::TRANSFERT, EntryFlagEnum::HIDDEN],
        ];
        yield 'HIDDEN and PERIODIC_ENTRY' => [
            [EntryFlagEnum::HIDDEN, EntryFlagEnum::PERIODIC_ENTRY],
        ];
        yield 'TRANSFERT, PERIODIC_ENTRY and HIDDEN' => [
            [EntryFlagEnum::TRANSFERT, EntryFlagEnum::PERIODIC_ENTRY, EntryFlagEnum::HIDDEN],
        ];
    }

    #[DataProvider('multipleNonEditableFlagsProvider')]
    public function testIsNotEditableWithMultipleNonEditableFlags(array $flags): void
    {
        $entry = $this->createEntry($flags);

        self::assertFalse(
            $entry->isEditable(),
            sprintf('Entry with flags [%s] should not be editable', implode(', ', array_map(fn ($f) => $f->value, $flags)))
        );
    }

    public function testIsNotEditableWithAllNonEditableFlags(): void
    {
        $entry = $this->createEntry(Entry::NON_EDITABLE_FLAGS);

        self::assertFalse($entry->isEditable(), 'Entry with all non-editable flags should not be editable');
    }

    public function testNonEditableFlagsConstant(): void
    {
        $expectedFlags = [
            EntryFlagEnum::BALANCE,
            EntryFlagEnum::TRANSFERT,
            EntryFlagEnum::PERIODIC_ENTRY,
            EntryFlagEnum::HIDDEN,
        ];

        self::assertEquals($expectedFlags, Entry::NON_EDITABLE_FLAGS);
        self::assertCount(4, Entry::NON_EDITABLE_FLAGS);
    }

    public function testEditabilityChangesWithFlags(): void
    {
        $entry = $this->createEntry([]);
        self::assertTrue($entry->isEditable(), 'Entry should start as editable');

        $entry->setFlags([EntryFlagEnum::BALANCE]);
        self::assertFalse($entry->isEditable(), 'Entry should become non-editable after adding BALANCE flag');

        $entry->setFlags([EntryFlagEnum::BALANCE, EntryFlagEnum::HIDDEN]);
        self::assertFalse($entry->isEditable(), 'Entry should remain non-editable with multiple non-editable flags');

        $entry->setFlags([]);
        self::assertTrue($entry->isEditable(), 'Entry should become editable again after removing all flags');
    }
}
