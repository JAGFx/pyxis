<?php

namespace App\Infrastructure\Cqs\EventListener;

use App\Infrastructure\Cqs\Service\CorrelationIdService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RequestCorrelationIdListener
{
    public const string REQUEST_ATTRIBUTE_NAME = '_correlation_id';

    public function __construct(
        private readonly CorrelationIdService $correlationIdService,
    ) {
    }

    #[AsEventListener(event: RequestEvent::class, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request       = $event->getRequest();
        $correlationId = $this->correlationIdService->activateCorrelationId(
            $request->headers->get(CorrelationIdService::HEADER_NAME)
        );

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
            $response->headers->set(CorrelationIdService::HEADER_NAME, $correlationId);
        }
    }
}
