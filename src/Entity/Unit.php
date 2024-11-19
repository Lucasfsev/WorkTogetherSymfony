<?php

namespace App\Entity;

use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\ManyToMany(targetEntity: Intervention::class, inversedBy: 'units')]
    private Collection $interventions;

    #[ORM\ManyToOne(inversedBy: 'units')]
    #[ORM\JoinColumn (nullable:false)]
    private ?TypeUnit $type = null;

    #[ORM\ManyToOne(inversedBy: 'units')]
    #[ORM\JoinColumn (nullable:false)]
    private ?StateUnit $state = null;

    #[ORM\ManyToOne(inversedBy: 'units')]
    #[ORM\JoinColumn (nullable:false)]
    private ?Bay $bay = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'units')]
    private Collection $orders;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->interventions->removeElement($intervention);

        return $this;
    }

    public function getType(): ?TypeUnit
    {
        return $this->type;
    }

    public function setType(?TypeUnit $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): ?StateUnit
    {
        return $this->state;
    }

    public function setState(?StateUnit $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getBay(): ?Bay
    {
        return $this->bay;
    }

    public function setBay(?Bay $bay): static
    {
        $this->bay = $bay;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addUnit($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removeUnit($this);
        }

        return $this;
    }
}
