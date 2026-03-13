<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Factory;

use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\AbstractRequestExportCommand;
use InvalidArgumentException;

final class RequestExportCommandFactory
{
    public const string DEFINITION_TAG = 'app.module.exporter.request_export.command';

    public const string ATTRIBUTE_KEY_NAME = 'name';

    public function __construct(
        /** @var array<string, string> */
        private readonly array $requestExportCommandMap = [],
    ) {
    }

    // TODO: Add unit tests
    public function create(string $name): AbstractRequestExportCommand
    {
        if (!array_key_exists($name, $this->requestExportCommandMap)) {
            throw new InvalidArgumentException(sprintf('Unsupported export command name: %s', $name));
        }

        /** @var AbstractRequestExportCommand $command */
        $command = new $this->requestExportCommandMap[$name]();

        return $command;
    }
}
