<?php

namespace App\Module\Exporter\Domain\Artifact\Security;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Artifact>
 */
class ArtifactVoter extends Voter
{
    public const string DOWNLOAD = 'ARTIFACT_DOWNLOAD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::DOWNLOAD !== $attribute) {
            return false;
        }

        return $subject instanceof Artifact;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Artifact $artifact */
        $artifact = $subject;

        return match ($attribute) {
            self::DOWNLOAD => $this->canDownload($artifact),
            default        => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canDownload(Artifact $artifact): bool
    {
        return $artifact->isFinished();
    }
}
