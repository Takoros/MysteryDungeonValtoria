<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Repository\DungeonRepository;
use App\Repository\RaidRepository;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[Table(name: '`character`')]
class Character
{
    public const GENDER_MALE = 'Mâle';
    public const GENDER_FEMALE = 'Femelle';
    public const MIN_AGE = 18;
    public const MAX_AGE = 44;

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
    public ?int $level = 0;

    #[ORM\Column(name: '`xp`')]
    public ?int $xp = 0;

    #[ORM\OneToOne(mappedBy: 'Character', cascade: ['persist', 'remove'])]
    private ?User $userI = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Stats $Stats = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Gear $Gear = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Inventory $Inventory = null;

    #[ORM\ManyToOne(inversedBy: 'Characters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $Species = null;

    #[ORM\ManyToMany(targetEntity: CombatLog::class, mappedBy: 'Characters')]
    private Collection $CombatLogs;

    #[ORM\OneToMany(mappedBy: 'Character', targetEntity: Rotation::class)]
    private Collection $rotations;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Timers $Timers = null;

    #[ORM\Column]
    private ?bool $isShiny = null;

    #[ORM\ManyToOne(inversedBy: 'Explorers')]
    private ?DungeonInstance $currentExplorationDungeonInstance = null;

    #[ORM\ManyToOne(inversedBy: 'Explorers')]
    private ?RaidInstance $currentExplorationRaidInstance = null;

    public function __construct()
    {
        $this->CombatLogs = new ArrayCollection();
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

    public function getGear(): ?Gear
    {
        return $this->Gear;
    }

    public function setGear(?Gear $Gear): self
    {
        $this->Gear = $Gear;

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->Inventory;
    }

    public function setInventory(?Inventory $Inventory): self
    {
        $this->Inventory = $Inventory;

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
     * @return Collection<int, CombatLog>
     */
    public function getCombatLogs(): Collection
    {
        return $this->CombatLogs;
    }

    public function getLastTenCombatLogs(): array
    {
        $combatLogs = $this->CombatLogs->toArray();

        usort($combatLogs, function($a, $b){
            return $b->getDateCreation()->getTimestamp() - $a->getDateCreation()->getTimestamp();
        });

        return array_slice($combatLogs, 0, 10);
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
    
    public function getCurrentExplorationDungeonInstance(): ?DungeonInstance
    {
        return $this->currentExplorationDungeonInstance;
    }

    public function setCurrentExplorationDungeonInstance(?DungeonInstance $currentExplorationDungeonInstance): self
    {
        $this->currentExplorationDungeonInstance = $currentExplorationDungeonInstance;

        return $this;
    }

    public function getTimers(): ?Timers
    {
        return $this->Timers;
    }

    public function setTimers(Timers $Timers): self
    {
        $this->Timers = $Timers;

        return $this;
    }

    public function isShiny(): ?bool
    {
        return $this->isShiny;
    }

    public function setIsShiny(bool $isShiny): self
    {
        $this->isShiny = $isShiny;

        return $this;
    }

    public function getCurrentExplorationRaidInstance(): ?RaidInstance
    {
        return $this->currentExplorationRaidInstance;
    }

    public function setCurrentExplorationRaidInstance(?RaidInstance $currentExplorationRaidInstance): self
    {
        $this->currentExplorationRaidInstance = $currentExplorationRaidInstance;

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                                  SHORTCUTS                                 */
    /* -------------------------------------------------------------------------- */

    public function getLevel(): ?int
    {
        return $this->Stats->getLevel();
    }

    public function getXp(): ?int
    {
        
        return $this->Stats->getXp();
    }

    public function getTypes(): array
    {
        $speciesType = $this->getSpecies()->getType();

        return $speciesType->toArray();
    }

    /* -------------------------------------------------------------------------- */
    /*                                 XP & LEVELS                                */
    /* -------------------------------------------------------------------------- */

    public function gainXp($xpAmount): void
    {
        $this->Stats->gainXp($xpAmount);
    }

    public function getXPCeil(): int
    {
        return $this->Stats->getXPCeil();
    }

    public function getXPPercentage(): int
    {
        return $this->Stats->getXPPercentage();
    }

    /* -------------------------------------------------------------------------- */
    /*                             Rotations & Opener                             */
    /* -------------------------------------------------------------------------- */

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

    /**
     * Returns in a array all the Attacks available for a character
     */
    public function getAvailableAttacks(TypeRepository $typeRepository, AttackRepository $attackRepository): array
    {
        $adventurerType = $typeRepository->findOneBy(['name' => 'Aventurier']);

        $allCharacterAttackTypes = [$adventurerType];

        foreach ($this->getTypes() as $type) {
            $allCharacterAttackTypes[] = $type;
        }

        $attackList = [];
        foreach ($allCharacterAttackTypes as $type) {
            foreach ($attackRepository->findAvailableAttacksForLevelAndType($this->getLevel(), $type) as $attack) {
                $attackList[] = $attack;
            }
        }
        
        return $attackList;
    }

    /* -------------------------------------------------------------------------- */
    /*                     Exploration & Available Activities                     */
    /* -------------------------------------------------------------------------- */

    public function getAvailableDungeons(DungeonRepository $dungeonRepository): array
    {
        // Does not require exploration
        $DUNGEON_ONE = $dungeonRepository->find('DUNGEON_ONE');

        // Requires to be explorated
        // Todo with Exploration's Update

        return [$DUNGEON_ONE];
    }

    public function getAvailableRaids(RaidRepository $raidRepository): array
    {
        // Does not require exploration
        $RAID_ONE = $raidRepository->find('RAID_ONE');

        // Require to be explorated
        // TODO with Exploration's Update

        return [$RAID_ONE];
    }

    /* -------------------------------------------------------------------------- */
    /*                             CHARACTER CREATION                             */
    /* -------------------------------------------------------------------------- */

    /**
     * Verify data of the new Character
     */
    public function createNewCharacter(User $user, AttackRepository $attackRepository, EntityManagerInterface $em, TranslatorInterface $translator): self
    {
        /**
         * Verify that user does not already have a character
         */
        if($user->getCharacter() !== null){
            throw new Exception($translator->trans('vous_possedez_deja_un_personnage', [], 'app'), 400);
        }

        /**
         * Verify that the name is correct
         */
        if(preg_match('~[0-9]+~', $this->name)){
            throw new Exception($translator->trans('le_nom_choisi_est_incorrect', [], 'app'), 400);
        }

        /**
         * Verify that the age is correct
         */
        if($this->age < self::MIN_AGE || $this->age > self::MAX_AGE){
            throw new Exception($translator->trans('l_age_choisi_est_incorrect', [], 'app'), 400);
        }

        $OpenerRotation = new Rotation();
        $OpenerRotation->initNewRotation(Rotation::TYPE_OPENER,$this, $attackRepository);

        $Rotation = new Rotation();
        $Rotation->initNewRotation(Rotation::TYPE_ROTATION, $this, $attackRepository);

        $Stats = new Stats();
        $Stats->initNewCharacterStats();

        $Timers = new Timers();
        $Timers->initNewTimers();

        $Gear = new Gear();
        $Gear->initNewGear($em);

        $Inventory = new Inventory();
        $Inventory->initNewInventory();

        $this->setUserI($user)
             ->setStats($Stats)
             ->setTimers($Timers)
             ->setGear($Gear)
             ->setInventory($Inventory)
             ->setIsShiny(false);

        $em->persist($OpenerRotation);
        $em->persist($Rotation);
        $em->persist($Stats);
        $em->persist($Timers);
        $em->persist($Gear);
        $em->persist($Inventory);
        $em->persist($this);

        $user->addRoles(['ROLE_CHARACTER']);

        $em->flush();

        return $this;
    }
}
