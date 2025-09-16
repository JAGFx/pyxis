<?php

namespace App\Domain\Entry\Entity;

enum EntryFlagEnum: string
{
    case BALANCE        = 'balance';
    case TRANSFERT      = 'transfert';
    case PERIODIC_ENTRY = 'periodic_entry';
    case HIDDEN         = 'hidden';

    public function label(): string
    {
        return 'entry_flag_enum.form.options.' . $this->name;
    }

    public function humanize(): string
    {
        return 'entry_flag_enum.options.' . $this->name;
    }
}
