<?php

namespace App\Shared\Cqs\Bus;

use App\Shared\Cqs\Message\Command\CommandInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

readonly class MessageBus
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
        $this->commandBus->dispatch($command);
    }

    /**
     * @throws ExceptionInterface
     */
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        /** @var ?HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
