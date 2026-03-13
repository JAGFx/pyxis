<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Module\Exporter\Infrastructure\RequestExport\Attribute\AsExportCommand;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\AbstractRequestExportCommand;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * @see RequestExportAccountListHandler
 */
#[AsMessage('async')]
#[AsExportCommand]
class RequestExportAccountListCommand extends AbstractRequestExportCommand implements RequestExportCommandInterface
{
    public const string NAME = 'export_account_list';

    public function getName(): string
    {
        return self::NAME;
    }
}
