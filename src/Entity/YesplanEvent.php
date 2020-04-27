<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\YesplanEventRepository")
 */
class YesplanEvent
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
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eventDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $genre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $marketing_budget;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publication_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $presale_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $in_sale_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tickets_available;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tickets_reserved;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ticket_capacity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tickets_blocked;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tickets_allocated;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeInterface $eventDate): self
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getMarketingBudget(): ?string
    {
        return $this->marketing_budget;
    }

    public function setMarketingBudget(?string $marketing_budget): self
    {
        $this->marketing_budget = $marketing_budget;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publication_date;
    }

    public function setPublicationDate(?\DateTimeInterface $publication_date): self
    {
        $this->publication_date = $publication_date;

        return $this;
    }

    public function getPresaleDate(): ?\DateTimeInterface
    {
        return $this->presale_date;
    }

    public function setPresaleDate(?\DateTimeInterface $presale_date): self
    {
        $this->presale_date = $presale_date;

        return $this;
    }

    public function getInSaleDate(): ?\DateTimeInterface
    {
        return $this->in_sale_date;
    }

    public function setInSaleDate(?\DateTimeInterface $in_sale_date): self
    {
        $this->in_sale_date = $in_sale_date;

        return $this;
    }

    public function getTicketsAvailable(): ?int
    {
        return $this->tickets_available;
    }

    public function setTicketsAvailable(?int $tickets_available): self
    {
        $this->tickets_available = $tickets_available;

        return $this;
    }

    public function getTicketsReserved(): ?int
    {
        return $this->tickets_reserved;
    }

    public function setTicketsReserved(?int $tickets_reserved): self
    {
        $this->tickets_reserved = $tickets_reserved;

        return $this;
    }

    public function getTicketCapacity(): ?int
    {
        return $this->ticket_capacity;
    }

    public function setTicketCapacity(?int $ticket_capacity): self
    {
        $this->ticket_capacity = $ticket_capacity;

        return $this;
    }

    public function getTicketsBlocked(): ?int
    {
        return $this->tickets_blocked;
    }

    public function setTicketsBlocked(?int $tickets_blocked): self
    {
        $this->tickets_blocked = $tickets_blocked;

        return $this;
    }

    public function getTicketsAllocated(): ?int
    {
        return $this->tickets_allocated;
    }

    public function setTicketsAllocated(?int $tickets_allocated): self
    {
        $this->tickets_allocated = $tickets_allocated;

        return $this;
    }
}
