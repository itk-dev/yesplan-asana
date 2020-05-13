<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AsanaEventRepository")
 */
class AsanaEvent
{
     /*
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $createdInAsana;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $createdInNewEvents;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $createdInNewEventsOnline;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $createdInFewTickets;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $createdInLastMinute;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedInAsana(): ?bool
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

    public function getCreatedInNewEvents(): ?bool
    {
        return $this->createdInNewEvents;
    }

    public function setCreatedInNewEvents(?bool $createdInNewEvents): self
    {
        $this->createdInNewEvents = $createdInNewEvents;

        return $this;
    }

    public function getCreatedInNewEventsOnline(): ?bool
    {
        return $this->createdInNewEventsOnline;
    }

    public function setCreatedInNewEventsOnline(?bool $createdInNewEventsOnline): self
    {
        $this->createdInNewEventsOnline = $createdInNewEventsOnline;

        return $this;
    }

    public function getCreatedInFewTickets(): ?bool
    {
        return $this->createdInFewTickets;
    }

    public function setCreatedInFewTickets(?bool $createdInFewTickets): self
    {
        $this->createdInFewTickets = $createdInFewTickets;

        return $this;
    }

    public function getCreatedInLastMinute(): ?bool
    {
        return $this->createdInLastMinute;
    }

    public function setCreatedInLastMinute(?bool $createdInLastMinute): self
    {
        $this->createdInLastMinute = $createdInLastMinute;

        return $this;
    }
}
