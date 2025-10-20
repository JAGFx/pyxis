<?php

namespace App\Infrastructure\OpenTelemetry\Logging\Factory;

use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LoggerProviderFactory
{
    public function __construct(
        #[Autowire(env: 'OTEL_EXPORTER_OTLP_ENDPOINT')]
        private readonly string $endpoint,

        #[Autowire(env: 'OTEL_SERVICE_NAME')]
        private readonly string $serviceName,

        #[Autowire(env: 'APP_ENV')]
        private readonly string $environment,

        #[Autowire(env: 'int:OTEL_BSP_MAX_QUEUE_SIZE')]
        private readonly int $maxQueueSize = 2048,

        #[Autowire(env: 'int:OTEL_BSP_SCHEDULE_DELAY')]
        private readonly int $scheduleDelay = 5000,

        #[Autowire(env: 'int:OTEL_BSP_MAX_EXPORT_BATCH_SIZE')]
        private readonly int $maxExportBatchSize = 512,
    ) {
    }
    private static ?LoggerProviderInterface $instance = null;

    public function create(): LoggerProviderInterface
    {
        // Singleton pattern pour éviter de recréer le provider à chaque fois
        if (self::$instance instanceof LoggerProviderInterface) {
            return self::$instance;
        }

        $resource = ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => $this->serviceName,
            'deployment.environment'         => $this->environment,
        ]));

        // Créer le transport GRPC
        $transport = new GrpcTransportFactory()->create(
            $this->endpoint . '/opentelemetry.proto.collector.logs.v1.LogsService/Export',
            'application/x-protobuf'
        );

        $logsExporter = new LogsExporter($transport);

        // Utiliser BatchLogRecordProcessor avec ClockFactory pour améliorer les performances
        $batchLogRecordProcessor = new BatchLogRecordProcessor(
            $logsExporter,
            ClockFactory::getDefault(),
            $this->maxQueueSize,
            $this->scheduleDelay,
            30000, // exportTimeoutMillis
            $this->maxExportBatchSize
        );

        self::$instance = LoggerProvider::builder()
            ->setResource($resource)
            ->addLogRecordProcessor($batchLogRecordProcessor)
            ->build();

        return self::$instance;
    }

    public function __destruct()
    {
        // Flush et shutdown au moment de la destruction du service
        if (self::$instance instanceof LoggerProviderInterface) {
            self::$instance->forceFlush();
            self::$instance->shutdown();
        }
    }
}
