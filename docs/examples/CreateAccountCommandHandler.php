<?php

declare(strict_types=1);

namespace App\Domain\Account\Handler;

use App\Domain\Account\Message\CreateAccountCommand;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Infrastructure\Logging\ActionLogger;

final readonly class CreateAccountCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ActionLogger $actionLogger,
        // ... autres dépendances
    ) {}

    public function __invoke(CreateAccountCommand $command): void
    {
        $this->actionLogger->logAction('Creating new account', [
            'account_name' => $command->name,
            'user_id' => $command->userId,
        ]);

        try {
            // ... logique métier de création de compte ...
            
            $this->actionLogger->logAction('Account created successfully', [
                'account_id' => $account->getId(),
            ]);
            
        } catch (\Exception $e) {
            $this->actionLogger->logError('Account creation failed', $e, [
                'command_data' => $command->name,
            ]);
            throw $e;
        }
    }
}
