<?php

namespace App\Shared\ValueObject;

use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Assignment\Form\AssignmentSearchType;
use App\Domain\Budget\Form\BudgetSearchType;

enum MenuConfigurationEntityEnum: string
{
    case ACCOUNT    = 'account';
    case ASSIGNMENT = 'assignment';
    case BUDGET     = 'budget';

    public function getSearchFormType(): string
    {
        return match ($this) {
            self::ACCOUNT    => AccountSearchType::class,
            self::ASSIGNMENT => AssignmentSearchType::class,
            self::BUDGET     => BudgetSearchType::class,
        };
    }

    public function getSearchLiveComponent(): string
    {
        return match ($this) {
            self::ACCOUNT    => 'AccountSearchForm',
            self::ASSIGNMENT => 'AssigmentSearchForm',
            self::BUDGET     => 'BudgetSearchForm',
        };
    }
}
