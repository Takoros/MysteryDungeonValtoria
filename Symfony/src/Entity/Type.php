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
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attackFile = null;

    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Attack::class)]
    private Collection $Attacks;

    #[ORM\ManyToMany(targetEntity: Species::class, mappedBy: 'Type')]
    private Collection $Species;

    #[ORM\ManyToMany(targetEntity: Character::class, mappedBy: 'Types')]
    private Collection $characters;

    public function __construct()
    {
        $this->Attacks = new ArrayCollection();
        $this->Species = new ArrayCollection();
        $this->characters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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
            $species->addType($this);
        }

        return $this;
    }

    public function removeSpecies(Species $species): self
    {
        if ($this->Species->removeElement($species)) {
            $species->removeType($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
            $character->addType($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->characters->removeElement($character)) {
            $character->removeType($this);
        }

        return $this;
    }
}
