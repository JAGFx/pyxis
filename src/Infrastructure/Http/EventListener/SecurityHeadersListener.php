<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Security headers on every response. Headers already set by a controller are kept.
 *
 * CSP notes:
 * - style-src 'unsafe-inline': style attributes (progress bars) and the Turbo progress bar <style>
 * - fonts.googleapis.com / fonts.gstatic.com: Fredoka font imported by assets/styles/bugr.css
 * - the web profiler adds its own nonces in dev and removes the CSP on its own pages
 */
final class SecurityHeadersListener
{
    public const array HEADERS = [
        'Content-Security-Policy' => "default-src 'self'; "
            . "script-src 'self'; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' data:; "
            . "connect-src 'self'; "
            . "object-src 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'",
        'X-Frame-Options'        => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy'        => 'same-origin',
        'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), payment=()',
    ];

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        foreach (self::HEADERS as $name => $value) {
            if (!$headers->has($name)) {
                $headers->set($name, $value);
            }
        }
    }
}
