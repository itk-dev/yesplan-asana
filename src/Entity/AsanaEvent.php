<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AsanaEventRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: AsanaEventRepository::class)]
class AsanaEvent
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;



    #[ORM\Id]
    #[ORM\Column(length:255)]
    private ?string $id;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInAsana = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedDate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInNewEvents = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInNewEventsOnline = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInFewTickets = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInLastMinute = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInNewEventsExternal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $createdInCalendar = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isCreatedInAsana(): ?bool
    {
        return $this->createdInAsana;
    }

    public function setCreatedInAsana(?bool $createdInAsana): self
    {
        $this->createdInAsana = $createdInAsana;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function isCreatedInNewEvents(): ?bool
    {
        return $this->createdInNewEvents;
    }

    public function setCreatedInNewEvents(?bool $createdInNewEvents): self
    {
        $this->createdInNewEvents = $createdInNewEvents;

        return $this;
    }

    public function isCreatedInNewEventsOnline(): ?bool
    {
        return $this->createdInNewEventsOnline;
    }

    public function setCreatedInNewEventsOnline(?bool $createdInNewEventsOnline): self
    {
        $this->createdInNewEventsOnline = $createdInNewEventsOnline;

        return $this;
    }

    public function isCreatedInFewTickets(): ?bool
    {
        return $this->createdInFewTickets;
    }

    public function setCreatedInFewTickets(?bool $createdInFewTickets): self
    {
        $this->createdInFewTickets = $createdInFewTickets;

        return $this;
    }

    public function isCreatedInLastMinute(): ?bool
    {
        return $this->createdInLastMinute;
    }

    public function setCreatedInLastMinute(?bool $createdInLastMinute): self
    {
        $this->createdInLastMinute = $createdInLastMinute;

        return $this;
    }

    public function isCreatedInNewEventsExternal(): ?bool
    {
        return $this->createdInNewEventsExternal;
    }

    public function setCreatedInNewEventsExternal(?bool $createdInNewEventsExternal): self
    {
        $this->createdInNewEventsExternal = $createdInNewEventsExternal;

        return $this;
    }

    public function isCreatedInCalendar(): ?bool
    {
        return $this->createdInCalendar;
    }

    public function setCreatedInCalendar(?bool $createdInCalendar): self
    {
        $this->createdInCalendar = $createdInCalendar;

        return $this;
    }
}
