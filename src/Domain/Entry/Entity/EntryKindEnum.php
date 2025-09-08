<?php

namespace App\Domain\Entry\Entity;

enum EntryKindEnum: string
{
    case DEFAULT   = 'default';
    case BALANCING = 'balancing';
}
