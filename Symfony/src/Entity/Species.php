<?php

namespace App\Entity;

use App\Repository\SpeciesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpeciesRepository::class)]
class Species
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isPlayable = null;

    #[ORM\OneToMany(mappedBy: 'Species', targetEntity: Character::class)]
    private Collection $Characters;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
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

    public function isIsPlayable(): ?bool
    {
        return $this->isPlayable;
    }

    public function setIsPlayable(bool $isPlayable): self
    {
        $this->isPlayable = $isPlayable;

        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->Characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->Characters->contains($character)) {
            $this->Characters->add($character);
            $character->setSpecies($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->Characters->removeElement($character)) {
            // set the owning side to null (unless already changed)
            if ($character->getSpecies() === $this) {
                $character->setSpecies(null);
            }
        }

        return $this;
    }
}
