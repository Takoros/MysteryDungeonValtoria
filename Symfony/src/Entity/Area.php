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

    #[ORM\Column]
    private ?bool $isExplorable = null;

    #[ORM\OneToMany(mappedBy: 'Area', targetEntity: Raid::class)]
    private Collection $Raids;

    public function __construct()
    {
        $this->Dungeons = new ArrayCollection();
        $this->Raids = new ArrayCollection();
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

    public function isIsExplorable(): ?bool
    {
        return $this->isExplorable;
    }

    public function setIsExplorable(bool $isExplorable): self
    {
        $this->isExplorable = $isExplorable;

        return $this;
    }

    /**
     * @return Collection<int, Raid>
     */
    public function getRaids(): Collection
    {
        return $this->Raids;
    }

    public function addRaid(Raid $raid): self
    {
        if (!$this->Raids->contains($raid)) {
            $this->Raids->add($raid);
            $raid->setArea($this);
        }

        return $this;
    }

    public function removeRaid(Raid $raid): self
    {
        if ($this->Raids->removeElement($raid)) {
            // set the owning side to null (unless already changed)
            if ($raid->getArea() === $this) {
                $raid->setArea(null);
            }
        }

        return $this;
    }
}
