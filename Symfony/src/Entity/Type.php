<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attackFile = null;

    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Species::class)]
    private Collection $Species;

    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Attack::class)]
    private Collection $Attacks;

    public function __construct()
    {
        $this->Species = new ArrayCollection();
        $this->Attacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAttackFile(): ?string
    {
        return $this->attackFile;
    }

    public function setAttackFile(?string $attackFile): self
    {
        $this->attackFile = $attackFile;

        return $this;
    }

    /**
     * @return Collection<int, Species>
     */
    public function getSpecies(): Collection
    {
        return $this->Species;
    }

    public function addSpecies(Species $species): self
    {
        if (!$this->Species->contains($species)) {
            $this->Species->add($species);
            $species->setType($this);
        }

        return $this;
    }

    public function removeSpecies(Species $species): self
    {
        if ($this->Species->removeElement($species)) {
            // set the owning side to null (unless already changed)
            if ($species->getType() === $this) {
                $species->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Attack>
     */
    public function getAttacks(): Collection
    {
        return $this->Attacks;
    }

    public function addAttack(Attack $attack): self
    {
        if (!$this->Attacks->contains($attack)) {
            $this->Attacks->add($attack);
            $attack->setType($this);
        }

        return $this;
    }

    public function removeAttack(Attack $attack): self
    {
        if ($this->Attacks->removeElement($attack)) {
            // set the owning side to null (unless already changed)
            if ($attack->getType() === $this) {
                $attack->setType(null);
            }
        }

        return $this;
    }
}
