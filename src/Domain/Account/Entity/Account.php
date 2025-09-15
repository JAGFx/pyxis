<?php

namespace App\Domain\Account\Entity;

use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Entity\Entry;
use App\Shared\Entity\EntityCollectionTrait;
use App\Shared\Entity\NameableTrait;
use App\Shared\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    use TimestampableTrait;
    use NameableTrait;
    use EntityCollectionTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Entry::class, fetch: 'EXTRA_LAZY', indexBy: 'createdAt')]
    private Collection $entries;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    /**
     * @var Collection<int, Assignment>
     */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Assignment::class, fetch: 'EXTRA_LAZY')]
    private Collection $assignments;

    public function __construct()
    {
        $this->createdAt   = new DateTimeImmutable();
        $this->entries     = new ArrayCollection();
        $this->assignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    /**
     * @param Collection<int, Entry> $entries
     */
    public function setEntries(Collection $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function addEntry(Entry $entry): self
    {
        $this->addToCollection($this->entries, $entry, 'setAccount');

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        $this->removeFromCollection($this->entries, $entry, 'setAccount');

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    /**
     * @param Collection<int, Assignment> $assignments
     */
    public function setAssignments(Collection $assignments): self
    {
        $this->assignments = $assignments;

        return $this;
    }

    public function addAssignment(Assignment $assignment): self
    {
        $this->addToCollection($this->assignments, $assignment, 'setAccount');

        return $this;
    }

    public function removeAssignment(Assignment $assignment): self
    {
        $this->removeFromCollection($this->assignments, $assignment, 'setAccount');

        return $this;
    }
}
