<?php

namespace App\Entity;

use App\Repository\DungeonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DungeonRepository::class)]
class Dungeon
{
    #[ORM\Id]
    #[ORM\Column]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'Dungeons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $Area = null;

    #[ORM\OneToMany(mappedBy: 'Dungeon', targetEntity: DungeonInstance::class)]
    private Collection $DungeonInstances;

    #[ORM\Column]
    private ?int $maxMonsterLevel = null;

    #[ORM\Column]
    private ?int $minMonsterLevel = null;

    #[ORM\Column(length: 255)]
    private ?string $size = null;

    #[ORM\Column]
    private array $monsterLivingList = [];

    public function __construct()
    {
        $this->DungeonInstances = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
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

    public function getArea(): ?Area
    {
        return $this->Area;
    }

    public function setArea(?Area $Area): self
    {
        $this->Area = $Area;

        return $this;
    }

    /**
     * @return Collection<int, DungeonInstance>
     */
    public function getDungeonInstances(): Collection
    {
        return $this->DungeonInstances;
    }

    public function addDungeonInstance(DungeonInstance $dungeonInstance): self
    {
        if (!$this->DungeonInstances->contains($dungeonInstance)) {
            $this->DungeonInstances->add($dungeonInstance);
            $dungeonInstance->setDungeon($this);
        }

        return $this;
    }

    public function removeDungeonInstance(DungeonInstance $dungeonInstance): self
    {
        if ($this->DungeonInstances->removeElement($dungeonInstance)) {
            // set the owning side to null (unless already changed)
            if ($dungeonInstance->getDungeon() === $this) {
                $dungeonInstance->setDungeon(null);
            }
        }

        return $this;
    }

    public function getMaxMonsterLevel(): ?int
    {
        return $this->maxMonsterLevel;
    }

    public function setMaxMonsterLevel(int $maxMonsterLevel): self
    {
        $this->maxMonsterLevel = $maxMonsterLevel;

        return $this;
    }

    public function getMinMonsterLevel(): ?int
    {
        return $this->minMonsterLevel;
    }

    public function setMinMonsterLevel(int $minMonsterLevel): self
    {
        $this->minMonsterLevel = $minMonsterLevel;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMonsterLivingList(): array
    {
        return $this->monsterLivingList;
    }

    public function setMonsterLivingList(array $monsterLivingList): self
    {
        $this->monsterLivingList = $monsterLivingList;

        return $this;
    }
}
