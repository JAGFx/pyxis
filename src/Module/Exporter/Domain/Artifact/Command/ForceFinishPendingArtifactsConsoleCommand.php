<?php

namespace App\Module\Exporter\Domain\Artifact\Command;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Artifact\Message\Command\ForceFinishPendingArtifacts\ForceFinishPendingArtifactsCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Throwable;

#[AsCommand('pyxis:exporter:artifact:force-finish-pending')]
#[AsPeriodicTask('P1D', '01:00:00')]
class ForceFinishPendingArtifactsConsoleCommand
{
    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(name: 'max-age-in-minutes', shortcut: 'm')]
        ?int $maxAgeInMinutes = null,
    ): int {
        // TODO: Continue here: Add command to disable artifact and remove document stored
        // TODO: See to add lock on ressource

        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            $forceFinishPendingArtifactsCommand = new ForceFinishPendingArtifactsCommand($maxAgeInMinutes);

            $this->messageBus->dispatch($forceFinishPendingArtifactsCommand);

            $symfonyStyle->success('All pending artifacts that are blocked for more than the specified age have been marked as finished.');

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $symfonyStyle->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }
}
