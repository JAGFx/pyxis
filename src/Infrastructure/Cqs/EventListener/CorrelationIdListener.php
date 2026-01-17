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

    #[AsEventListener(event: RequestEvent::class, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request       = $event->getRequest();
        $correlationId = $request->headers->get(self::HEADER_NAME) ?? new Ulid()->toBase32();

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
