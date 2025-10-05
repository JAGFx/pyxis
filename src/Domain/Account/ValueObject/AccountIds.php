<?php

namespace App\Domain\Account\ValueObject;

readonly class AccountIds
{
    public function __construct(
        private int $total,
        /**
         * @var array<int> $ids
         */
        private array $ids = [],
    ) {
    }

    /**
     * @return array<int>
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
