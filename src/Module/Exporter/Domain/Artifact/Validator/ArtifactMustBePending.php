<?php

namespace App\Module\Exporter\Domain\Artifact\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ArtifactMustBePending extends Constraint
{
    public string $message = 'exporter.artifact.must_be_pending';

    public function __construct(?array $groups = null)
    {
        parent::__construct(groups: $groups);
    }
}
