<?php

namespace App\Shared\Utils;

use App\Shared\ValueObject\SearchFormTargetEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchFormUrl
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generateSearchFormUrl(SearchFormTargetEnum $target): string
    {
        return $this->urlGenerator->generate('front_home_search_form', [
            'target' => $target->value,
        ]);
    }
}
