<?php

namespace App\Shared\ValueObject;

use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Assignment\Form\AssignmentSearchType;
use App\Domain\Budget\Form\BudgetSearchType;
use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\PeriodicEntry\Form\PeriodicEntrySearchType;

enum MenuConfigurationEntityEnum: string
{
    case ACCOUNT        = 'account';
    case ASSIGNMENT     = 'assignment';
    case BUDGET         = 'budget';
    case PERIODIC_ENTRY = 'periodic_entry';
    case ENTRY          = 'entry';

    public function getSearchFormType(): string
    {
        return match ($this) {
            self::ACCOUNT        => AccountSearchType::class,
            self::ASSIGNMENT     => AssignmentSearchType::class,
            self::BUDGET         => BudgetSearchType::class,
            self::PERIODIC_ENTRY => PeriodicEntrySearchType::class,
            self::ENTRY          => EntrySearchType::class,
        };
    }

    public function getSearchLiveComponent(): string
    {
        return match ($this) {
            self::ACCOUNT        => 'AccountSearchForm',
            self::ASSIGNMENT     => 'AssigmentSearchForm',
            self::BUDGET         => 'BudgetSearchForm',
            self::PERIODIC_ENTRY => 'PeriodicEntrySearchForm',
            self::ENTRY          => 'EntrySearchForm',
        };
    }
}
