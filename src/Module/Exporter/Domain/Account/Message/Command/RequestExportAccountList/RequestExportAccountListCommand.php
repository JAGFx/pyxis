<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Cqs\Message\Command\TranslatableTrait;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\AbstractRequestExportCommand;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * @see RequestExportAccountListHandler
 */
#[AsMessage('async')]
class RequestExportAccountListCommand extends AbstractRequestExportCommand implements RequestExportCommandInterface
{
    use TranslatableTrait;

    public function getTarget(): string
    {
        return Account::class;
    }
}
