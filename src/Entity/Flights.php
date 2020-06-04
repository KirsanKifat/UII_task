<?php

namespace App\Entity;

use App\Repository\FlightsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FlightsRepository::class)
 */
class Flights
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canceled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ticket_sale_end;

    /**
     * @ORM\OneToMany(targetEntity=Tickets::class, mappedBy="flights", orphanRemoval=true)
     */
    private $ticket_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    public function __construct()
    {
        $this->ticket_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;

        return $this;
    }

    public function getTicketSaleEnd(): ?bool
    {
        return $this->ticket_sale_end;
    }

    public function setTicketSaleEnd(bool $ticket_sale_end): self
    {
        $this->ticket_sale_end = $ticket_sale_end;

        return $this;
    }

    /**
     * @return Collection|Tickets[]
     */
    public function getTicketId(): Collection
    {
        return $this->ticket_id;
    }

    public function addTicketId(Tickets $ticketId): self
    {
        if (!$this->ticket_id->contains($ticketId)) {
            $this->ticket_id[] = $ticketId;
            $ticketId->setFlightsId($this);
        }

        return $this;
    }

    public function removeTicketId(Tickets $ticketId): self
    {
        if ($this->ticket_id->contains($ticketId)) {
            $this->ticket_id->removeElement($ticketId);
            // set the owning side to null (unless already changed)
            if ($ticketId->getFlightsId() === $this) {
                $ticketId->setFlightsId(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
