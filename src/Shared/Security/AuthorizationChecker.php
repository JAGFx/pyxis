<?php

namespace App\Shared\Security;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

readonly class AuthorizationChecker
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function denyAccessUnlessGranted(string $attribute, ?object $subject = null): void
    {
        if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
            $subjectClass = null !== $subject ? get_class($subject) : 'null';

            throw new AccessDeniedHttpException(sprintf('Not granted for %s on %s', $attribute, $subjectClass));
        }
    }
}
