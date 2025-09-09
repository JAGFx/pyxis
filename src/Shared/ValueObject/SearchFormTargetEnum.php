<?php

namespace App\Shared\ValueObject;

use App\Domain\Account\Form\AccountSearchType;

enum SearchFormTargetEnum: string
{
    case ACCOUNT = 'account';

    public function getFormType(): string
    {
        return match ($this) {
            self::ACCOUNT => AccountSearchType::class,
        };
    }

    public function getLiveComponent(): string
    {
        return match ($this) {
            self::ACCOUNT => 'AccountSearchForm',
        };
    }
}
