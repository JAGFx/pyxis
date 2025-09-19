<?php

namespace App\Domain\Assignment\Message\Command;

use App\Domain\Assignment\Entity\Assignment;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class AssigmentRemoveCommand implements CommandInterface
{
    public function __construct(
        #[NotNull]
        private ?Assignment $assignment = null,
    ) {
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): AssigmentRemoveCommand
    {
        $this->assignment = $assignment;

        return $this;
    }
}
