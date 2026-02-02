<?php

namespace App\Module\Exporter\Infrastructure\Storage;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

final readonly class StorageSystem
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,

        #[Autowire(env: 'EXPORTER_FILE_SYSTEM_PATH')]
        private string $fileSystemPath,

        #[Autowire(env: 'EXPORTER_S3_REGION')]
        private string $s3Region,

        #[SensitiveParameter]
        #[Autowire(env: 'EXPORTER_S3_KEY')]
        private string $s3Key,

        #[SensitiveParameter]
        #[Autowire(env: 'EXPORTER_S3_SECRET')]
        private string $s3Secret,

        #[Autowire(env: 'EXPORTER_S3_BUCKET')]
        private string $s3Bucket,
    ) {
    }

    /**
     * @param array<string, string> $config
     *
     * @throws FilesystemException
     */
    public function write(DocumentInterface $document, string $contents, ?array $config = null): void
    {
        $this->getMountManager()->write($document->getPath(), $contents, $config ?? [
            'visibility'           => 'private',
            'directory_visibility' => 'private',
        ]);
    }

    /**
     * @throws FilesystemException
     */
    public function read(DocumentInterface $document): string
    {
        return $this->getMountManager()->read($document->getPath());
    }

    /**
     * @throws FilesystemException
     */
    public function getHttpStreamResponse(DocumentInterface $document): Response
    {
        $content = $this->read($document);

        $response = new Response($content);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $document->getFileName()
        ));

        return $response;
    }

    private function getMountManager(): MountManager
    {
        return new MountManager([
            StorageEnum::FILE_SYSTEM->value => new Filesystem($this->getFileSystemAdapter()),
            StorageEnum::S3->value          => new Filesystem($this->getS3Adapter()),
        ]);
    }

    private function getFileSystemAdapter(): LocalFilesystemAdapter
    {
        return new LocalFilesystemAdapter($this->projectDir . '/' . $this->fileSystemPath);
    }

    private function getS3Adapter(): AwsS3V3Adapter
    {
        $s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->s3Region,
            'credentials' => [
                'key'    => $this->s3Key,
                'secret' => $this->s3Secret,
            ],
        ]);

        return new AwsS3V3Adapter(
            $s3Client,
            $this->s3Bucket
        );
    }
}
