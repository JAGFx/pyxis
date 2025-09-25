<?php

namespace App\Infrastructure\Cqs\Bus;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;
use App\Shared\Validation\ValidationGroupEnum;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ValidationStamp;
use Throwable;

readonly class MessageBus
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function dispatch(CommandInterface|QueryInterface $command): mixed
    {
        try {
            if ($command instanceof QueryInterface) {
                return $this->dispatchQuery($command);
            }

            $this->dispatchCommand($command);

            return null;
        } catch (HandlerFailedException $handlerFailedException) {
            $previous = $handlerFailedException->getPrevious();

            if ($previous instanceof NotFoundHttpException
                || $previous instanceof AccessDeniedHttpException
            ) {
                throw $previous;
            }

            throw $handlerFailedException;
        }
    }

    /**
     * @throws ExceptionInterface
     */
    private function dispatchCommand(CommandInterface $command): void
    {
        $message = new Envelope($command)->with(
            new ValidationStamp([
                ValidationGroupEnum::Default->value,
                ValidationGroupEnum::Business->value,
            ]))
        ;
        $this->commandBus->dispatch($message);
    }

    /**
     * @throws ExceptionInterface
     */
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $message = new Envelope($query)->with(
            new ValidationStamp([ValidationGroupEnum::Default->value])
        );

        $envelope = $this->queryBus->dispatch($message);

        /** @var ?HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
