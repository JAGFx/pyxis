<?php

namespace App\Shared\MenuConfiguration\ValueObject;

readonly class MenuConfiguration
{
    public function __construct(
        private ?string $createUrl = null,
        private ?string $searchFormUrl = null,
    ) {
    }

    public function getCreateUrl(): ?string
    {
        return $this->createUrl;
    }

    public function getSearchFormUrl(): ?string
    {
        return $this->searchFormUrl;
    }
}
