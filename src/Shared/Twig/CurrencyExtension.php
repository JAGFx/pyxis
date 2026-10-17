<?php

namespace App\Shared\Twig;

use Twig\Attribute\AsTwigFilter;

class CurrencyExtension
{
    /**
     * @param int $amount In cents
     */
    #[AsTwigFilter('currency')]
    public function currency(int $amount, bool $withSign = true): string
    {
        if ($withSign) {
            $result = ($amount < 0)
                ? ''
                : '+';
        } else {
            $result = '';
        }

        $result .= number_format($amount / 100, 2, '.', ' ');

        return $result . ' €';
    }
}
