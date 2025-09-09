<?php

namespace App\Shared\ValueObject;

use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Assignment\Form\AssignmentSearchType;

enum SearchFormTargetEnum: string
{
    case ACCOUNT    = 'account';
    case ASSIGNMENT = 'assignment';

    public function getFormType(): string
    {
        return match ($this) {
            self::ACCOUNT    => AccountSearchType::class,
            self::ASSIGNMENT => AssignmentSearchType::class,
        };
    }

    public function getLiveComponent(): string
    {
        return match ($this) {
            self::ACCOUNT    => 'AccountSearchForm',
            self::ASSIGNMENT => 'AssigmentSearchForm',
        };
    }
}
