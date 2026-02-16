<?php

namespace App\Module\Exporter\Domain\Artifact\Entity;

use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Entity\HasIntIdentifierTrait;
use App\Shared\Entity\HasUuidIdentifierTrait;
use App\Shared\Entity\IntIdentifierInterface;
use App\Shared\Entity\TimestampableTrait;
use App\Shared\Entity\UuidIdentifierInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ArtifactRepository::class)]
#[Table(name: 'exporter_artifact')]
#[UniqueEntity('uuid')]
#[ORM\HasLifecycleCallbacks]
class Artifact implements IntIdentifierInterface, UuidIdentifierInterface
{
    use HasIntIdentifierTrait;
    use HasUuidIdentifierTrait;
    use TimestampableTrait;

    #[ORM\Column]
    private string $command;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $parent = null;

    #[ORM\Column(nullable: true)]
    private ?string $documentName = null;

    #[ORM\Column(nullable: true)]
    private ?string $documentPath = null;

    #[ORM\Column(nullable: true, enumType: StorageEnum::class)]
    private ?StorageEnum $storage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $finishedAt = null;

    public function __construct(string $command, ?Uuid $uuid = null)
    {
        $this->command = $command;

        $this->setUuid($uuid);
    }

    public function getStatus(): ArtifiactStatusEnum
    {
        if (!is_null($this->finishedAt)) {
            return ArtifiactStatusEnum::DONE;
        }

        return ArtifiactStatusEnum::PENDING;
    }

    public function isFinished(): bool
    {
        return ArtifiactStatusEnum::DONE === $this->getStatus();
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): Artifact
    {
        $this->command = $command;

        return $this;
    }

    public function getParent(): ?Artifact
    {
        return $this->parent;
    }

    public function setParent(?Artifact $parent): Artifact
    {
        $this->parent = $parent;

        return $this;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function setDocumentName(?string $documentName): Artifact
    {
        $this->documentName = $documentName;

        return $this;
    }

    public function getDocumentPath(): ?string
    {
        return $this->documentPath;
    }

    public function setDocumentPath(?string $documentPath): Artifact
    {
        $this->documentPath = $documentPath;

        return $this;
    }

    public function getStorage(): ?StorageEnum
    {
        return $this->storage;
    }

    public function setStorage(?StorageEnum $storage): Artifact
    {
        $this->storage = $storage;

        return $this;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?DateTimeImmutable $finishedAt): Artifact
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }
}
