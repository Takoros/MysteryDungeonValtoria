<?php

namespace App\Entity;

use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'Area', targetEntity: Dungeon::class)]
    private Collection $Dungeons;

    public function __construct()
    {
        $this->Dungeons = new ArrayCollection();
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

    /**
     * @return Collection<int, Dungeon>
     */
    public function getDungeons(): Collection
    {
        return $this->Dungeons;
    }

    public function addDungeon(Dungeon $dungeon): self
    {
        if (!$this->Dungeons->contains($dungeon)) {
            $this->Dungeons->add($dungeon);
            $dungeon->setArea($this);
        }

        return $this;
    }

    public function removeDungeon(Dungeon $dungeon): self
    {
        if ($this->Dungeons->removeElement($dungeon)) {
            // set the owning side to null (unless already changed)
            if ($dungeon->getArea() === $this) {
                $dungeon->setArea(null);
            }
        }

        return $this;
    }
}
