<?php

namespace App\Infrastructure\Cqs\Bus;

use App\Infrastructure\Cqs\Validation\ValidationGroupEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;
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
     * @param StampInterface[] $additionalsStamps
     *
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function dispatch(CommandInterface|QueryInterface $command, array $additionalsStamps = []): mixed
    {
        try {
            if ($command instanceof QueryInterface) {
                return $this->dispatchQuery($command, $additionalsStamps);
            }

            $this->dispatchCommand($command, $additionalsStamps);

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
     * @param StampInterface[] $additionalsStamps
     *
     * @throws ExceptionInterface
     */
    private function dispatchCommand(CommandInterface $command, array $additionalsStamps = []): void
    {
        $message = new Envelope($command)->with(
            new ValidationStamp([
                ValidationGroupEnum::Default->value,
                ValidationGroupEnum::Business->value,
            ]),
            ...$additionalsStamps
        );
        $this->commandBus->dispatch($message);
    }

    /**
     * @param StampInterface[] $additionalsStamps
     *
     * @throws ExceptionInterface
     */
    private function dispatchQuery(QueryInterface $query, array $additionalsStamps = []): mixed
    {
        $message = new Envelope($query)->with(
            new ValidationStamp([ValidationGroupEnum::Default->value]),
            ...$additionalsStamps
        );

        $envelope = $this->queryBus->dispatch($message);

        /** @var ?HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
