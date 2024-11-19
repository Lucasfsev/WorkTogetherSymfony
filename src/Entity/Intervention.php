<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $report = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $dateStart = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $dateEnd = null;

    /**
     * @var Collection<int, Unit>
     */
    #[ORM\ManyToMany(targetEntity: Unit::class, mappedBy: 'interventions')]
    private Collection $units;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn (nullable:false)]
    private ?TypeIntervention $type = null;

    public function __construct()
    {
        $this->units = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReport(): ?string
    {
        return $this->report;
    }

    public function setReport(?string $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getDateStart(): ?\DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeImmutable $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeImmutable $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return Collection<int, Unit>
     */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function addUnit(Unit $unit): static
    {
        if (!$this->units->contains($unit)) {
            $this->units->add($unit);
            $unit->addIntervention($this);
        }

        return $this;
    }

    public function removeUnit(Unit $unit): static
    {
        if ($this->units->removeElement($unit)) {
            $unit->removeIntervention($this);
        }

        return $this;
    }

    public function getType(): ?TypeIntervention
    {
        return $this->type;
    }

    public function setType(?TypeIntervention $type): static
    {
        $this->type = $type;

        return $this;
    }
}
