<?php

namespace App\Entity;

use App\Repository\StatsRepository;
use App\Service\Dungeon\MonsterCharacter;
use App\Service\Raid\MonsterRaidCharacter;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: StatsRepository::class)]
class Stats
{
    public const MAX_LEVEL = 10;
    public const PRIMARY_STATS = ['vitality', 'strength', 'stamina', 'power', 'bravery', 'presence', 'impassiveness'];
    public const SECONDARY_STATS = ['agility', 'coordination', 'speed'];

    public const SPEND_STAT_POINT_INCREASE_AMOUNT = 1;
    public const SPEND_STAT_POINT_INCREASE_AMOUNT_HP = 3;
    public const SPEND_STAT_POINT_MAX_PER_LEVEL = 2;
    public const SPEND_STAT_POINT_MAX_PER_LEVEL_HP = 6;

    public const BASE_STAT_INCREASE_PER_LEVEL = 4;
    public const BASE_STAT_HP_INCREASE_PER_LEVEL = 12;

    public const BASE_STAT_LEVEL_ONE = 24;
    public const BASE_STAT_HP_LEVEL_ONE = 88;

    public const PRIMARY_STAT_POINT_PER_LEVEL = 4;
    public const SECONDARY_STAT_POINT_PER_LEVEL = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $level = null;

    #[ORM\Column]
    private ?int $xp = null;

    #[ORM\Column]
    private ?int $vitality = null;

    #[ORM\Column]
    private ?int $strength = null;

    #[ORM\Column]
    private ?int $stamina = null;

    #[ORM\Column]
    private ?int $power = null;

    #[ORM\Column]
    private ?int $bravery = null;

    #[ORM\Column]
    private ?int $presence = null;

    #[ORM\Column]
    private ?int $impassiveness = null;

    #[ORM\Column]
    private ?int $agility = null;

    #[ORM\Column]
    private ?int $coordination = null;

    #[ORM\Column]
    private ?int $speed = null;

    #[ORM\Column]
    private ?int $actionPoint = null;

    #[ORM\Column]
    private ?int $primaryStatPoint = null;

    #[ORM\Column]
    private ?int $secondaryStatPoint = null;

    #[ORM\OneToOne(mappedBy: 'Stats', cascade: ['persist', 'remove'])]
    private ?Character $character = null;

    public MonsterCharacter|MonsterRaidCharacter|null $monsterCharacter = null;

    /* ---------------------------------- Utils --------------------------------- */

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $value): self
    {
        $this->level = $value;

        return $this;
    }

    public function getXp(): ?int
    {
        return $this->xp;
    }

    public function setXp(int $value): self
    {
        $this->xp = $value;

        return $this;
    }

    /* ------------------------------ WEAPON POWER ------------------------------ */

    public function getWeaponPower(): ?float
    {
        if($this->monsterCharacter){
            $Weapon = $this->monsterCharacter->getGear()->getWeapon();
        }
        else {
            $Weapon = $this->character->getGear()->getWeapon();
        }

        if($Weapon !== null){
            return $Weapon->getProperties()['weaponPower'];
        }

        return 1.80;
    }

    /* -------------------------------- VITALITY -------------------------------- */

    public function getVitality(): ?int
    {
        return $this->getBaseStatForLevel(true) + $this->vitality + $this->getGearVitality();
    }

    public function getVitalitySpentPoints(): ?int {
        return $this->vitality;
    }

    public function getBaseVitality(): ?int {
        return $this->getBaseStatForLevel(true);
    }

    public function getGearVitality(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('vitality');
    }

    public function setVitality(int $value): self
    {
        $this->vitality = $value;

        return $this;
    }

    /* -------------------------------- STRENGTH -------------------------------- */

    public function getStrength(): ?int
    {
        return $this->getBaseStatForLevel() + $this->strength + $this->getGearStrength();
    }

    public function getStrengthSpentPoints(): ?int 
    {
        return $this->strength;
    }

    public function getBaseStrength(): ?int 
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearStrength(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('strength');
    }

    public function setStrength(int $value): self
    {
        $this->strength = $value;

        return $this;
    }

    /* --------------------------------- STAMINA -------------------------------- */

    public function getStamina(): ?int
    {
        return $this->getBaseStatForLevel() + $this->stamina + $this->getGearStamina();
    }

    public function getStaminaSpentPoints(): ?int
    {
        return $this->stamina;
    }

    public function getBaseStamina(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearStamina(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('stamina');
    }

    public function setStamina(int $value): self
    {
        $this->stamina = $value;

        return $this;
    }

    /* ---------------------------------- POWER --------------------------------- */

    public function getPower(): ?int
    {
        return $this->getBaseStatForLevel() + $this->power + $this->getGearPower();
    }

    public function getPowerSpentPoints(): ?int
    {
        return $this->power;
    }

    public function getBasePower(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearPower(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('power');
    }

    public function setPower(int $value): self
    {
        $this->power = $value;

        return $this;
    }

    /* --------------------------------- BRAVERY -------------------------------- */


    public function getBravery(): ?int
    {
        return $this->getBaseStatForLevel() + $this->bravery + $this->getGearBravery();
    }

    public function getBraverySpentPoints(): ?int
    {
        return $this->bravery;
    }

    public function getBaseBravery(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearBravery(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('bravery');
    }

    public function setBravery(int $value): self
    {
        $this->bravery = $value;

        return $this;
    }

    /* -------------------------------- PRESENCE -------------------------------- */

    public function getPresence(): ?int
    {
        return $this->getBaseStatForLevel() + $this->presence + $this->getGearPresence();
    }

    public function getPresenceSpentPoints(): ?int
    {
        return $this->presence;
    }

    public function getBasePresence(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearPresence(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('presence');
    }

    public function setPresence(int $value): self
    {
        $this->presence = $value;

        return $this;
    }

    /* ------------------------------ IMPASSIVENESS ----------------------------- */

    public function getImpassiveness(): ?int
    {
        return $this->getBaseStatForLevel() + $this->impassiveness + $this->getGearImpassiveness();
    }

    public function getImpassivenessSpentPoints(): ?int
    {
        return $this->impassiveness;
    }

    public function getBaseImpassiveness(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearImpassiveness(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('impassiveness');
    }

    public function setImpassiveness(int $value): self
    {
        $this->impassiveness = $value;

        return $this;
    }

    /* --------------------------------- AGILITY -------------------------------- */

    public function getAgility(): ?int
    {
        return $this->getBaseStatForLevel() + $this->agility + $this->getGearAgility();
    }

    public function getAgilitySpentPoints(): ?int
    {
        return $this->agility;
    }

    public function getBaseAgility(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearAgility(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('agility');
    }

    public function setAgility(int $value): self
    {
        $this->agility = $value;

        return $this;
    }

    /* ------------------------------ COORDINATION ------------------------------ */

    public function getCoordination(): ?int
    {
        return $this->getBaseStatForLevel() + $this->coordination + $this->getGearCoordination();
    }

    public function getCoordinationSpentPoints(): ?int
    {
        return $this->coordination;
    }

    public function getBaseCoordination(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearCoordination(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('coordination');
    }

    public function setCoordination(int $value): self
    {
        $this->coordination = $value;

        return $this;
    }

    /* ---------------------------------- SPEED --------------------------------- */

    public function getSpeed(): ?int
    {
        return $this->getBaseStatForLevel() + $this->speed + $this->getGearSpeed();
    }

    public function getSpeedSpentPoints(): ?int
    {
        return $this->speed;
    }

    public function getBaseSpeed(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    public function getGearSpeed(): ?int {
        if($this->monsterCharacter){
            $Gear = $this->monsterCharacter->getGear();
        }
        else {
            $Gear = $this->character->getGear();
        }

        return $Gear->getStatBonuses('speed');
    }

    public function setSpeed(int $value): self
    {
        $this->speed = $value;

        return $this;
    }

    /* --------------------------------- OTHERS --------------------------------- */

    public function getActionPoint(): ?int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $value): self
    {
        $this->actionPoint = $value;

        return $this;
    }

    public function getPrimaryStatPoint(): ?int
    {
        return $this->primaryStatPoint;
    }

    public function getSecondaryStatPoint(): ?int
    {
        return $this->secondaryStatPoint;
    }

    public function getMaxPoints(bool $isHP = false): int
    {
        if($isHP){
            return $this->level * self::SPEND_STAT_POINT_MAX_PER_LEVEL_HP;
        }
        else {
            return $this->level * self::SPEND_STAT_POINT_MAX_PER_LEVEL;
        }
    }

    /**
     * Returns the base value of a stat for the current level
     */
    private function getBaseStatForLevel(bool $isHP = false){
        if($isHP){
            return self::BASE_STAT_HP_LEVEL_ONE + (self::BASE_STAT_HP_INCREASE_PER_LEVEL * $this->level);
        }
        else {
            return self::BASE_STAT_LEVEL_ONE + (self::BASE_STAT_INCREASE_PER_LEVEL * $this->level);
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                                 XP & Levels                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Adds XP to the XP Bar of the Character
     */
    public function gainXp(int $xpAmount): void
    {
        $this->xp += $xpAmount;

        if($this->hasEnoughXP()){
            $this->levelUp();
        }
    }

    /**
     * Increase the Character's level
     */
    private function levelUp(): void
    {
        if($this->level >= self::MAX_LEVEL){
            $this->xp = $this->getXPCeil();

            return ;
        }

        $this->level++;
        $this->xp -= $this->getXPCeil();
        $this->primaryStatPoint += self::PRIMARY_STAT_POINT_PER_LEVEL;
        $this->secondaryStatPoint += self::SECONDARY_STAT_POINT_PER_LEVEL;
    }

    /**
     * Returns the XP needed for leveling up
     */
    public function getXPCeil(): int
    {
        return intval(round(10.1 * pow($this->getLevel(), 2.83)));
    }

    /**
     * Returns the percent of the XP bar fill
     */
    public function getXPPercentage(): int
    {
        return ceil($this->getXp() * 100 / $this->getXPCeil());
    }

    /**
     * Returns true if the XP needed for leveling up is acquired
     */
    public function hasEnoughXP(): bool
    {
        if($this->getXp() >= $this->getXPCeil()){
            return true;
        }

        return false;
    }

    /* -------------------------------------------------------------------------- */
    /*                             Stats Manipulation                             */
    /* -------------------------------------------------------------------------- */

    /**
     * Inits all the class properties for a level one character.
     */
    public function initNewCharacterStats(): void
    {
        $this->level = 1;
        $this->xp = 0;
        $this->initStatsForLevel(1);
    }

    /**
     * Inits (And Resets) Stat Points (Primary & Secondary) for the current level
     */
    public function initStatsForLevel(): void
    {
        $this->vitality = 0;
        $this->strength = 0;
        $this->stamina = 0;
        $this->power = 0;
        $this->bravery = 0;
        $this->presence = 0;
        $this->impassiveness = 0;
        $this->agility = 0;
        $this->coordination = 0;
        $this->speed = 0;
        $this->actionPoint = 6;

        $this->primaryStatPoint = self::PRIMARY_STAT_POINT_PER_LEVEL * $this->level;
        $this->secondaryStatPoint = self::SECONDARY_STAT_POINT_PER_LEVEL * $this->level;
    }
    
    /**
     * Makes a Stat Increase by spending a corresponding Stat Point
     */
    public function spendStatPoint($statName, TranslatorInterface $translator): array
    {
        if(in_array($statName, self::PRIMARY_STATS)){
            if($this->primaryStatPoint > 0){
                $this->spendStatPointIncreaseStat($statName, $translator);
                $this->primaryStatPoint--;

                $callable = 'get'.ucfirst($statName);
                return [
                    'statusCode' => 200,
                    'newStatValue' => $this->$callable(),
                    'newPrimaryStatPointsValue' => $this->primaryStatPoint
                ];
            }
            else {
                throw new Exception($translator->trans("pas_assez_de_points_de_stat_primaire",[], 'app'), 400);
            }
        }
        else if(in_array($statName, self::SECONDARY_STATS)){
            if($this->secondaryStatPoint > 0){
                $this->spendStatPointIncreaseStat($statName, $translator);
                $this->secondaryStatPoint--;
                
                $callable = 'get'.ucfirst($statName);
                return [
                    'statusCode' => 200,
                    'newStatValue' => $this->$callable(),
                    'newSecondaryStatPointsValue' => $this->secondaryStatPoint
                ];
            }
            else {
                throw new Exception($translator->trans("pas_assez_de_points_de_stat_secondaire", [], 'app'), 400);
            }
        }
        else {
            throw new Exception("statName not found in PRIMARY_STATS or SECONDARY_STATS", 500);
        }
    }

    /**
     * Increase the stat asked by one state
     */
    private function spendStatPointIncreaseStat($statName, TranslatorInterface $translator): void
    {
        $translatedStat = $translator->trans($statName, [], 'app');

        if($statName === 'vitality'){
            if($this->$statName === (self::SPEND_STAT_POINT_MAX_PER_LEVEL_HP * $this->level)){
                throw new Exception($translator->trans('deja_au_max_pour_votre_niveau', ['%statName%' => ucfirst($translatedStat)], 'app'), 400);
            }
            else {
                $this->$statName += self::SPEND_STAT_POINT_INCREASE_AMOUNT_HP;
            }
        }
        else {
            if($this->$statName === (self::SPEND_STAT_POINT_MAX_PER_LEVEL * $this->level)){
                throw new Exception($translator->trans('deja_au_max_pour_votre_niveau', ['%statName%' => ucfirst($translatedStat)], 'app'), 400);
            }
            else {
                $this->$statName += self::SPEND_STAT_POINT_INCREASE_AMOUNT;
            }
        }
    }
}
