<?php

namespace App\Entity;

use App\Repository\RaidInstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\JsonResponse;

#[ORM\Entity(repositoryClass: RaidInstanceRepository::class)]
class RaidInstance
{
    const RAID_STATUS_PREPARATION = 'raid_status_preparation';
    const RAID_STATUS_EXPLORATION = 'raid_status_exploration';
    const RAID_STATUS_TERMINATION = 'raid_status_termination';

    const RAID_MAX_NUMBERS_OF_EXPLORERS = 8;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'currentExplorationRaidInstance', targetEntity: Character::class)]
    private Collection $Explorers;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Raid $Raid = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Character $leader = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $currentExplorersRoom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\OneToMany(mappedBy: 'raidInstance', targetEntity: CombatLog::class)]
    private Collection $fights;

    public function __construct()
    {
        $this->Explorers = new ArrayCollection();
        $this->fights = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getExplorers(): array
    {
        $explorers = [];

        foreach ($this->Explorers as $explorer) {
            $explorers[] = $explorer;
        }

        return $explorers;
    }

    public function addExplorer(Character $explorer): self
    {
        if (!$this->Explorers->contains($explorer)) {
            $this->Explorers->add($explorer);
            $explorer->setCurrentExplorationRaidInstance($this);
        }

        return $this;
    }

    public function removeExplorer(Character $explorer): self
    {
        if ($this->Explorers->removeElement($explorer)) {
            // set the owning side to null (unless already changed)
            if ($explorer->getCurrentExplorationRaidInstance() === $this) {
                $explorer->setCurrentExplorationRaidInstance(null);
            }
        }

        return $this;
    }

    public function getRaid(): ?Raid
    {
        return $this->Raid;
    }

    public function setRaid(?Raid $Raid): self
    {
        $this->Raid = $Raid;

        return $this;
    }

    public function getLeader(): ?Character
    {
        return $this->leader;
    }

    public function setLeader(?Character $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrentExplorersRoom(): ?int
    {
        return $this->currentExplorersRoom;
    }

    public function setCurrentExplorersRoom(int $currentExplorersRoom): self
    {
        $this->currentExplorersRoom = $currentExplorersRoom;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return Collection<int, CombatLog>
     */
    public function getFights(): Collection
    {
        return $this->fights;
    }

    public function addFight(CombatLog $fight): self
    {
        if (!$this->fights->contains($fight)) {
            $this->fights->add($fight);
            $fight->setRaidInstance($this);
        }

        return $this;
    }

    public function removeFight(CombatLog $fight): self
    {
        if ($this->fights->removeElement($fight)) {
            // set the owning side to null (unless already changed)
            if ($fight->getRaidInstance() === $this) {
                $fight->setRaidInstance(null);
            }
        }

        return $this;
    }

    public function deleteRaidInstance(EntityManagerInterface $em): void
    {
        foreach ($this->getFights() as $combatLog) {
            $combatLog->setRaidInstance(null);
        }

        $this->setLeader(null);
        $em->remove($this);
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */
    
    public function explorerLeaveRaid(Character $character, EntityManagerInterface $em): JsonResponse
    {
        if($this->getStatus() === self::RAID_STATUS_PREPARATION){
            $this->removeExplorer($character);

            if(count($this->getExplorers()) < 1){
                $this->deleteRaidInstance($em);
            }
            else if($character === $this->getLeader()){
                $this->setLeader($this->getExplorers()[0]);
            }

            return new JsonResponse([
                'message' => 'Raid quitté avec succès.'
            ], 200);
        }
        else if($this->getStatus() === self::RAID_STATUS_EXPLORATION){
            // TODO
        }
        else if($this->getStatus() === self::RAID_STATUS_TERMINATION){
            // TODO
        }
    }
}
