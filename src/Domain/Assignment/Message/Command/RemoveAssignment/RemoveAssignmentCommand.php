<?php

namespace App\Domain\Assignment\Message\Command\RemoveAssignment;

use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see RemoveAssignmentHandler
 */
class RemoveAssignmentCommand implements CommandInterface
{
    public function __construct(
        private int $assignmentId,
    ) {
    }

    public function getAssignmentId(): int
    {
        return $this->assignmentId;
    }

    public function setAssignmentId(int $assignmentId): RemoveAssignmentCommand
    {
        $this->assignmentId = $assignmentId;

        return $this;
    }
}
