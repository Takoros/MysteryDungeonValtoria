<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[Table(name: '`character`')]
class Character
{
    const GENDER_MALE = 'Mâle';
    const GENDER_FEMALE = 'Femelle';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: '`name`',length: 30)]
    private ?string $name = null;

    #[ORM\Column(name: '`gender`',length: 10)]
    private ?string $gender = null;

    #[ORM\Column(name: '`age`')]
    private ?int $age = null;

    #[ORM\Column(name: '`description`', length: 200, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: '`level`')]
    private ?int $level = null;

    #[ORM\Column(name: '`xp`')]
    private ?int $xp = null;

    #[ORM\Column(name: '`stat_points`')]
    private ?int $statPoints = null;

    #[ORM\Column(name: '`rank`')]
    private ?int $rank = null;

    #[ORM\OneToOne(mappedBy: 'Character', cascade: ['persist', 'remove'])]
    private ?User $userI = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Stats $Stats = null;

    #[ORM\ManyToOne(inversedBy: 'Characters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $Species = null;

    #[ORM\ManyToMany(targetEntity: Attack::class, inversedBy: 'Characters')]
    private Collection $Attacks;

    #[ORM\ManyToMany(targetEntity: CombatLog::class, mappedBy: 'Characters')]
    private Collection $CombatLogs;

    #[ORM\ManyToMany(targetEntity: Guild::class, inversedBy: 'Characters')]
    private Collection $Guild;

    #[ORM\OneToMany(mappedBy: 'Character', targetEntity: MissionHistory::class)]
    private Collection $MissionHistories;

    #[ORM\OneToMany(mappedBy: 'Character', targetEntity: Rotation::class)]
    private Collection $rotations;

    public function __construct()
    {
        $this->Attacks = new ArrayCollection();
        $this->CombatLogs = new ArrayCollection();
        $this->Guild = new ArrayCollection();
        $this->MissionHistories = new ArrayCollection();
        $this->rotations = new ArrayCollection();
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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getXp(): ?int
    {
        return $this->xp;
    }

    public function setXp(int $xp): self
    {
        $this->xp = $xp;

        return $this;
    }

    public function getStatPoints(): ?int
    {
        return $this->statPoints;
    }

    public function setStatPoints(int $statPoints): self
    {
        $this->statPoints = $statPoints;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getTypes(): array
    {
        $speciesType = $this->getSpecies()->getType();

        return $speciesType->toArray();
    }

    public function getUserI(): ?User
    {
        return $this->userI;
    }

    public function setUserI(?User $userI): self
    {
        // unset the owning side of the relation if necessary
        if ($userI === null && $this->userI !== null) {
            $this->userI->setCharacter(null);
        }

        // set the owning side of the relation if necessary
        if ($userI !== null && $userI->getCharacter() !== $this) {
            $userI->setCharacter($this);
        }

        $this->userI = $userI;

        return $this;
    }

    public function getStats(): ?Stats
    {
        return $this->Stats;
    }

    public function setStats(?Stats $Stats): self
    {
        $this->Stats = $Stats;

        return $this;
    }

    public function getSpecies(): ?Species
    {
        return $this->Species;
    }

    public function setSpecies(?Species $Species): self
    {
        $this->Species = $Species;

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
        }

        return $this;
    }

    public function removeAttack(Attack $attack): self
    {
        $this->Attacks->removeElement($attack);

        return $this;
    }

    /**
     * @return Collection<int, CombatLog>
     */
    public function getCombatLogs(): Collection
    {
        return $this->CombatLogs;
    }

    public function addCombatLog(CombatLog $combatLog): self
    {
        if (!$this->CombatLogs->contains($combatLog)) {
            $this->CombatLogs->add($combatLog);
            $combatLog->addCharacter($this);
        }

        return $this;
    }

    public function removeCombatLog(CombatLog $combatLog): self
    {
        if ($this->CombatLogs->removeElement($combatLog)) {
            $combatLog->removeCharacter($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Guild>
     */
    public function getGuild(): Collection
    {
        return $this->Guild;
    }

    public function addGuild(Guild $guild): self
    {
        if (!$this->Guild->contains($guild)) {
            $this->Guild->add($guild);
        }

        return $this;
    }

    public function removeGuild(Guild $guild): self
    {
        $this->Guild->removeElement($guild);

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
            $missionHistory->setCharacter($this);
        }

        return $this;
    }

    public function removeMissionHistory(MissionHistory $missionHistory): self
    {
        if ($this->MissionHistories->removeElement($missionHistory)) {
            // set the owning side to null (unless already changed)
            if ($missionHistory->getCharacter() === $this) {
                $missionHistory->setCharacter(null);
            }
        }

        return $this;
    }

    // ///
    // Services Functions
    // ///

    public function getXPCeil(): int
    {
        return intval(round(10.1 * pow($this->getLevel(), 2.83)));
    }

    public function hasEnoughXP(): bool
    {
        if($this->getXp() >= $this->getXPCeil()){
            return true;
        }

        return false;
    }

 /**
     * @return Collection<int, Rotation>
     */
    public function getRotations(): Collection
    {
        return $this->rotations;
    }

    /**
     * @return Rotation|null
     */
    public function getRotation(): ?Rotation
    {
        foreach ($this->rotations as $rotation) {
            if($rotation->getType() === Rotation::TYPE_ROTATION){
                return $rotation;
            }
        }

        return null;
    }

    /**
     * @return Rotation|null
     */
    public function getOpenerRotation(): ?Rotation
    {
        foreach ($this->rotations as $rotation) {
            if($rotation->getType() === Rotation::TYPE_OPENER){
                return $rotation;
            }
        }

        return null;
    }
}
