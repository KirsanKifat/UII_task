<?php

namespace App\Entity;

use App\Repository\TicketsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketsRepository::class)
 */
class Tickets
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $cost = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $seat;

    /**
     * @ORM\Column(type="boolean")
     */
    private $booking = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bought = false;

    /**
     * @ORM\ManyToOne(targetEntity=Flights::class, inversedBy="ticket_id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $flights;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="ticket")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getSeat(): ?int
    {
        return $this->seat;
    }

    public function setSeat(int $seat): self
    {
        $this->seat = $seat;

        return $this;
    }

    public function getBooking(): ?bool
    {
        return $this->booking;
    }

    public function setBooking(bool $booking): self
    {
        $this->booking = $booking;

        return $this;
    }

    public function getBought(): ?bool
    {
        return $this->bought;
    }

    public function setBought(bool $bought): self
    {
        $this->bought = $bought;

        return $this;
    }

    public function getFlights(): ?Flights
    {
        return $this->flights;
    }

    public function setFlights(?Flights $flights): self
    {
        $this->flights = $flights;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }
}
