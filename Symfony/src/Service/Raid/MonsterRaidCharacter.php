<?php

namespace App\Service\Raid;

use App\Entity\Rotation;
use App\Entity\Species;
use App\Entity\Stats;
use App\Repository\AttackRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use stdClass;

class MonsterRaidCharacter
{
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

    public function __construct(stdClass $config, SpeciesRepository $speciesRepository, AttackRepository $attackRepository)
    {
        $this->id = $config->id;
        $this->name = $config->name;
        $this->gender = $config->gender;
        $this->age = $config->age;
        $this->description = $config->description;
        $this->level = $config->level;
        $this->xp = $config->xp;
        $this->statPoints = $config->statPoints;
        $this->rank = $config->rank;
        
        $this->Stats = new Stats();
        $this->Stats->setVitality($config->Stats->vitality);
        $this->Stats->setStrength($config->Stats->strength);
        $this->Stats->setStamina($config->Stats->stamina);
        $this->Stats->setPower($config->Stats->power);
        $this->Stats->setBravery($config->Stats->bravery);
        $this->Stats->setPresence($config->Stats->presence);
        $this->Stats->setImpassiveness($config->Stats->impassiveness);
        $this->Stats->setAgility($config->Stats->agility);
        $this->Stats->setCoordination($config->Stats->coordination);
        $this->Stats->setSpeed($config->Stats->speed);
        $this->Stats->setActionPoint($config->Stats->actionPoint);

        $this->Species = $speciesRepository->find($config->Species);

        $Opener = new Rotation();
        $Opener->setType(Rotation::TYPE_OPENER)
               ->setAttackOne($attackRepository->find($config->Opener->ATTACK_ONE_ID))
               ->setAttackTwo($attackRepository->find($config->Opener->ATTACK_TWO_ID))
               ->setAttackThree($attackRepository->find($config->Opener->ATTACK_THREE_ID))
               ->setAttackFour($attackRepository->find($config->Opener->ATTACK_FOUR_ID))
               ->setAttackFive($attackRepository->find($config->Opener->ATTACK_FIVE_ID));
        
        $Rotation = new Rotation();
        $Rotation->setType(Rotation::TYPE_ROTATION)
                 ->setAttackOne($attackRepository->find($config->Rotation->ATTACK_ONE_ID))
                 ->setAttackTwo($attackRepository->find($config->Rotation->ATTACK_TWO_ID))
                 ->setAttackThree($attackRepository->find($config->Rotation->ATTACK_THREE_ID))
                 ->setAttackFour($attackRepository->find($config->Rotation->ATTACK_FOUR_ID))
                 ->setAttackFive($attackRepository->find($config->Rotation->ATTACK_FIVE_ID));
        
        $this->setRotations([$Opener, $Rotation]);
        $this->setIsShiny($config->isShiny);
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