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
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isPlayable = null;

    #[ORM\OneToMany(mappedBy: 'Species', targetEntity: Character::class)]
    private Collection $Characters;

    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'Species')]
    private Collection $Type;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
        $this->Type = new ArrayCollection();
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

    /**
     * @return Collection<int, Type>
     */
    public function getType(): Collection
    {
        return $this->Type;
    }

    public function addType(Type $type): self
    {
        if (!$this->Type->contains($type)) {
            $this->Type->add($type);
        }

        return $this;
    }

    public function removeType(Type $type): self
    {
        $this->Type->removeElement($type);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

}
