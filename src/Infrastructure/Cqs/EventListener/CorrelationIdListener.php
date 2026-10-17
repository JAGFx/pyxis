<?php

namespace App\Infrastructure\Cqs\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Uid\Ulid;

class CorrelationIdListener
{
    public const string HEADER_NAME = 'X-Correlation-ID';

    public const string REQUEST_ATTRIBUTE_NAME = '_correlation_id';

    private const string VALID_FORMAT = '/^[A-Za-z0-9._-]{1,64}$/';

    #[AsEventListener(event: RequestEvent::class, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request       = $event->getRequest();
        $correlationId = $request->headers->get(self::HEADER_NAME);

        // The header comes from the client: only reuse it when it looks like an identifier
        if (!is_string($correlationId) || 1 !== preg_match(self::VALID_FORMAT, $correlationId)) {
            $correlationId = new Ulid()->toBase32();
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE_NAME, $correlationId);
    }

    #[AsEventListener(event: ResponseEvent::class, priority: -10)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        /** @var ?string $correlationId */
        $correlationId = $request->attributes->get(self::REQUEST_ATTRIBUTE_NAME);
        if (null !== $correlationId) {
            $response->headers->set(self::HEADER_NAME, $correlationId);
        }
    }
}
