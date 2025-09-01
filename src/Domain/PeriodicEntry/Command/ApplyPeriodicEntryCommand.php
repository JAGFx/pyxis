<?php

namespace App\Domain\PeriodicEntry\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand('bugr:periodic-entry:apply')]
class ApplyPeriodicEntryCommand
{
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        //  TODO: Continue here
        $symfonyStyle = new SymfonyStyle($input, $output);

        try {
            //            dump('Plop');
            throw new Exception();
        } catch (Throwable $throwable) {
            $symfonyStyle->error($throwable->getMessage());
            //            $this->logger->error('Periodic entry command: Unable to execute cron job', [
            //                'exceptionMessage' => $exception->getMessage(),
            //                'exceptionClass'   => $exception::class,
            //            ]);

            return Command::FAILURE;
        }

        //        return Command::SUCCESS;
    }
}
