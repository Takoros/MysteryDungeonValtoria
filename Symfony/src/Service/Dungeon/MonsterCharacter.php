<?php

namespace App\Service\Dungeon;

use App\Entity\Character;
use App\Entity\Gear;
use App\Entity\Item;
use App\Entity\ItemRarityEnum;
use App\Entity\Rotation;
use App\Entity\Species;
use App\Entity\Stats;
use App\Repository\AttackRepository;
use App\Repository\TypeRepository;
use App\Service\Items\Weapon;

class MonsterCharacter
{
    const MONSTER_TYPE_DAMAGE_DEALER = 'monster-damage-dealer';
    const MONSTER_TYPE_TANK = 'monster-tank';

    private ?string $id = null;
    private ?string $name = null;
    private ?string $gender = null;
    private ?int $age = null;
    private ?string $description = null;
    private ?int $level = null;
    private ?int $xp = 0;
    private ?int $statPoints = 0;
    private ?int $rank = 0;
    public ?string $monsterType = null;
    private ?Stats $Stats = null;
    private ?Species $Species = null;
    private array $rotations;
    private ?bool $isShiny = null;
    private ?Gear $gear = null;

    public function __construct($minLevel, $maxLevel)
    {
        $genderChoice = [Character::GENDER_MALE, Character::GENDER_FEMALE];
        $genderKey = array_rand($genderChoice);
        $this->gender = $genderChoice[$genderKey];

        $monsterTypeChoice = [self::MONSTER_TYPE_DAMAGE_DEALER, self::MONSTER_TYPE_TANK];
        $monsterTypeKey = array_rand($monsterTypeChoice);

        $this->monsterType = $monsterTypeChoice[$monsterTypeKey];
        $this->level = rand($minLevel, $maxLevel);
        $this->age = rand(18, 44);
        $this->rotations = [];

        $diceRoll = rand(1,100);
        
        if($diceRoll === 1){
            $this->isShiny = true;
        }
        else {
            $this->isShiny = false;
        }

        $this->gear = new Gear();
        $weapon = new Item();

        $weaponPower = 2 + $this->level * 0.17 - 0.17;
        $weapon->makeWeapon('Griffes', ItemRarityEnum::ITEM_RARITY_COMMON, Weapon::WEAPON_TYPE_GAUNTELETS, $weaponPower, 1, 1, []);

        $this->gear->setWeapon($weapon);
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
        return $this->gear;
    }

    public function setGear(?Gear $Gear): self
    {
        $this->gear = $Gear;

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

    public function setRotations(array $rotations): self
    {
        $this->rotations = $rotations;

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

    /* -------------------------------------------------------------------------- */
    /*                              Service functions                             */
    /* -------------------------------------------------------------------------- */

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
}