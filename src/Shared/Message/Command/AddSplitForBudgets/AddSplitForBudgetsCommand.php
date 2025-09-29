<?php

namespace App\Shared\Message\Command\AddSplitForBudgets;

use App\Shared\Cqs\Message\Command\CommandInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see AddSplitForBudgetsHandler
 */
class AddSplitForBudgetsCommand implements CommandInterface
{
    public function __construct(
        #[Assert\NotNull]
        private ?int $periodicEntryId = null,
        private ?DateTimeImmutable $date = null,
    ) {
    }

    public function getPeriodicEntryId(): ?int
    {
        return $this->periodicEntryId;
    }

    public function setPeriodicEntryId(?int $periodicEntryId): AddSplitForBudgetsCommand
    {
        $this->periodicEntryId = $periodicEntryId;

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?DateTimeImmutable $date): AddSplitForBudgetsCommand
    {
        $this->date = $date;

        return $this;
    }
}
