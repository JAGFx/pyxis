<?php

namespace App\Domain\Account\Entity;

use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Entry\Entity\Entry;
use App\Shared\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    #[NotBlank]
    private string $name;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Entry::class, fetch: 'EXTRA_LAZY', indexBy: 'createdAt')]
    private Collection $entries;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $enable = true;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Account
    {
        $this->name = $name;

        return $this;
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
        if (!$this->entries->contains($entry)) {
            $entry->setAccount($this);
            $this->entries->add($entry);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): self
    {
        if ($this->entries->removeElement($entry) && $entry->getAccount() === $this) {
            $entry->setAccount(null);
        }

        return $this;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): Account
    {
        $this->enable = $enable;

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
    public function setAssignments(Collection $assignments): Account
    {
        $this->assignments = $assignments;

        return $this;
    }
}
