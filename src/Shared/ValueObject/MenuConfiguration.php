<?php

namespace App\Shared\ValueObject;

class MenuConfiguration {

    public function __construct(
        private readonly ?string $createUrl = null,
        private readonly ?string $searchFormUrl = null,
    )
    {
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