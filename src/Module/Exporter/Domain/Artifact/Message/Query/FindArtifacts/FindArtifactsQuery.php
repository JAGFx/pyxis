<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts;

use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginationInterface;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Shared\Cqs\Message\Query\QueryInterface;
use DateTimeImmutable;

/**
 * @see FindArtifactsHandler
 */
class FindArtifactsQuery implements OrderableInterface, PaginationInterface, QueryInterface
{
    use OrderableTrait;
    use PaginableTrait;

    public function __construct(
        private ?ArtifactStatusEnum $status = null,
        private ?int $maxAgeInMinutes = null,
        private ?DocumentTypeEnum $documentType = null,
        private ?DateTimeImmutable $startDate = null,
        private ?DateTimeImmutable $endDate = null,
    ) {
    }

    public function getStatus(): ?ArtifactStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?ArtifactStatusEnum $status): FindArtifactsQuery
    {
        $this->status = $status;

        return $this;
    }

    public function getMaxAgeInMinutes(): ?int
    {
        return $this->maxAgeInMinutes;
    }

    public function setMaxAgeInMinutes(?int $maxAgeInMinutes): FindArtifactsQuery
    {
        $this->maxAgeInMinutes = $maxAgeInMinutes;

        return $this;
    }

    public function getDocumentType(): ?DocumentTypeEnum
    {
        return $this->documentType;
    }

    public function setDocumentType(?DocumentTypeEnum $documentType): FindArtifactsQuery
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): FindArtifactsQuery
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): FindArtifactsQuery
    {
        $this->endDate = $endDate;

        return $this;
    }
}
