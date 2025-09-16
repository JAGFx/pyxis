<?php

namespace App\Domain\Entry\Entity;

enum EntryTypeEnum: string
{
    case TYPE_FORECAST = 'type-forecast';
    case TYPE_SPENT    = 'type-spent';

    public function humanize(): string
    {
        return match ($this) {
            self::TYPE_FORECAST => 'forecast',
            self::TYPE_SPENT    => 'balance',
        };
    }

    public function label(): string
    {
        return 'entry_type_enum.form.options.' . $this->name;
    }
}
