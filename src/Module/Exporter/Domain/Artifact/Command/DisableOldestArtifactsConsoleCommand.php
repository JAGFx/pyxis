<?php

namespace App\Module\Exporter\Domain\Artifact\Command;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Artifact\Message\Command\DisableOldestArtifacts\DisableOldestArtifactsCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Throwable;

#[AsCommand('pyxis:exporter:artifact:disable-oldest')]
#[AsPeriodicTask('P1D', '02:00:00')]
class DisableOldestArtifactsConsoleCommand
{
    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(name: 'max-age-in-days', shortcut: 'd')]
        ?int $maxAgeInDays = null,
    ): int {
        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            $disableOldestArtifactsCommand = new DisableOldestArtifactsCommand($maxAgeInDays);

            $this->messageBus->dispatch($disableOldestArtifactsCommand);

            $symfonyStyle->success('All oldest artifacts have been disabled.');

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $symfonyStyle->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }
}
