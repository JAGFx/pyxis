<?php

namespace App\Infrastructure\Cqs\Bus;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;
use App\Shared\Validation\ValidationGroupEnum;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ValidationStamp;

readonly class SymfonyMessageBus
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatch(CommandInterface|QueryInterface $command): mixed
    {
        if ($command instanceof QueryInterface) {
            return $this->dispatchQuery($command);
        }

        $this->dispatchCommand($command);

        return null;
    }

    /**
     * @throws ExceptionInterface
     */
    private function dispatchCommand(CommandInterface $command): void
    {
        $message = new Envelope($command)
            ->with(new ValidationStamp(['Default', ValidationGroupEnum::Business->value]));
        $this->commandBus->dispatch($message);
    }

    /**
     * @throws ExceptionInterface
     */
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $message = new Envelope($query)
            ->with(new ValidationStamp(['Default']));
        $envelope = $this->queryBus->dispatch($message);

        /** @var ?HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
