<?php

namespace App\Entity;

use App\Repository\GuildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuildRepository::class)]
class Guild
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Character::class, mappedBy: 'Guild')]
    private Collection $Characters;

    #[ORM\OneToMany(mappedBy: 'Guild', targetEntity: MissionHistory::class)]
    private Collection $MissionHistories;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
        $this->MissionHistories = new ArrayCollection();
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
            $character->addGuild($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->Characters->removeElement($character)) {
            $character->removeGuild($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, MissionHistory>
     */
    public function getMissionHistories(): Collection
    {
        return $this->MissionHistories;
    }

    public function addMissionHistory(MissionHistory $missionHistory): self
    {
        if (!$this->MissionHistories->contains($missionHistory)) {
            $this->MissionHistories->add($missionHistory);
            $missionHistory->setGuild($this);
        }

        return $this;
    }

    public function removeMissionHistory(MissionHistory $missionHistory): self
    {
        if ($this->MissionHistories->removeElement($missionHistory)) {
            // set the owning side to null (unless already changed)
            if ($missionHistory->getGuild() === $this) {
                $missionHistory->setGuild(null);
            }
        }

        return $this;
    }
}
