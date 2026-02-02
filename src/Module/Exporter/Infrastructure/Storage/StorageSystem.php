<?php

namespace App\Module\Exporter\Infrastructure\Storage;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

final readonly class StorageSystem
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * @param array<string, string> $config
     *
     * @throws FilesystemException
     */
    public function write(StorageEnum $storage, string $location, string $contents, array $config): void
    {
        $storage = $this->findAdapter($storage);
        $storage->write($location, $contents, $config);
    }

    /**
     * @throws FilesystemException
     */
    public function read(StorageEnum $storage, string $location): string
    {
        $storage = $this->findAdapter($storage);

        return $storage->read($location);
    }

    /**
     * @throws FilesystemException
     */
    public function getHttpStreamResponse(DocumentInterface $document): Response
    {
        $content = $this->read(
            $document->getStorage(),
            $document->getPath()
        );

        $response = new Response($content);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $document->getFileName()
        ));

        return $response;
    }

    private function findAdapter(StorageEnum $storage): Filesystem
    {
        return new Filesystem(match ($storage) {
            StorageEnum::FILE_SYSTEM => $this->getFileSystemAdapter(),
            StorageEnum::S3          => $this->getS3Adapter(),
        });
    }

    private function getFileSystemAdapter(): LocalFilesystemAdapter
    {
        return new LocalFilesystemAdapter($this->projectDir . '/private/');
    }

    private function getS3Adapter(): AwsS3V3Adapter
    {
        $s3Client = new S3Client([]);

        return new AwsS3V3Adapter(
            $s3Client,
            'bucket-name'
        );
    }
}
