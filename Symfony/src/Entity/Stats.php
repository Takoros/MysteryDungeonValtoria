<?php

namespace App\Entity;

use App\Repository\StatsRepository;
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

    /* ---------------------------------- Utils --------------------------------- */

    public TranslatorInterface $translator;

    /* ----------------------------------- -- ----------------------------------- */

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

    /* -------------------------------- VITALITY -------------------------------- */

    public function getVitality(): ?int
    {
        return $this->getBaseStatForLevel(true) + $this->vitality;
    }

    public function getVitalitySpentPoints(): ?int {
        return $this->vitality;
    }

    public function getBaseVitality(): ?int {
        return $this->getBaseStatForLevel(true);
    }

    /* -------------------------------- STRENGTH -------------------------------- */

    public function getStrength(): ?int
    {
        return $this->getBaseStatForLevel() + $this->strength;
    }

    public function getStrengthSpentPoints(): ?int 
    {
        return $this->strength;
    }

    public function getBaseStrength(): ?int 
    {
        return $this->getBaseStatForLevel();
    }

    /* --------------------------------- STAMINA -------------------------------- */

    public function getStamina(): ?int
    {
        return $this->getBaseStatForLevel() + $this->stamina;
    }

    public function getStaminaSpentPoints(): ?int
    {
        return $this->stamina;
    }

    public function getBaseStamina(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* ---------------------------------- POWER --------------------------------- */

    public function getPower(): ?int
    {
        return $this->getBaseStatForLevel() + $this->power;
    }

    public function getPowerSpentPoints(): ?int
    {
        return $this->power;
    }

    public function getBasePower(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* --------------------------------- BRAVERY -------------------------------- */


    public function getBravery(): ?int
    {
        return $this->getBaseStatForLevel() + $this->bravery;
    }

    public function getBraverySpentPoints(): ?int
    {
        return $this->bravery;
    }

    public function getBaseBravery(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* -------------------------------- PRESENCE -------------------------------- */

    public function getPresence(): ?int
    {
        return $this->getBaseStatForLevel() + $this->presence;
    }

    public function getPresenceSpentPoints(): ?int
    {
        return $this->presence;
    }

    public function getBasePresence(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* ------------------------------ IMPASSIVENESS ----------------------------- */

    public function getImpassiveness(): ?int
    {
        return $this->getBaseStatForLevel() + $this->impassiveness;
    }

    public function getImpassivenessSpentPoints(): ?int
    {
        return $this->impassiveness;
    }

    public function getBaseImpassiveness(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* --------------------------------- AGILITY -------------------------------- */

    public function getAgility(): ?int
    {
        return $this->getBaseStatForLevel() + $this->agility;
    }

    public function getAgilitySpentPoints(): ?int
    {
        return $this->agility;
    }

    public function getBaseAgility(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* ------------------------------ COORDINATION ------------------------------ */

    public function getCoordination(): ?int
    {
        return $this->getBaseStatForLevel() + $this->coordination;
    }

    public function getCoordinationSpentPoints(): ?int
    {
        return $this->coordination;
    }

    public function getBaseCoordination(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* ---------------------------------- SPEED --------------------------------- */

    public function getSpeed(): ?int
    {
        return $this->getBaseStatForLevel() + $this->speed;
    }

    public function getSpeedSpentPoints(): ?int
    {
        return $this->speed;
    }

    public function getBaseSpeed(): ?int
    {
        return $this->getBaseStatForLevel();
    }

    /* --------------------------------- OTHERS --------------------------------- */

    public function getActionPoint(): ?int
    {
        return $this->actionPoint;
    }

    public function getPrimaryStatPoint(): ?int
    {
        return $this->primaryStatPoint;
    }

    public function getSecondaryStatPoint(): ?int
    {
        return $this->secondaryStatPoint;
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
        $this->actionPoint = 0;
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

        $this->primaryStatPoint = self::PRIMARY_STAT_POINT_PER_LEVEL * $this->level;
        $this->secondaryStatPoint = self::SECONDARY_STAT_POINT_PER_LEVEL * $this->level;
    }
    
    /**
     * Makes a Stat Increase by spending a corresponding Stat Point
     */
    public function spendStatPoint($statName): array
    {
        if(in_array($statName, self::PRIMARY_STATS)){
            if($this->primaryStatPoint > 0){
                $this->spendStatPointIncreaseStat($statName);
                $this->primaryStatPoint--;

                $callable = 'get'.ucfirst($statName);
                return [
                    'statusCode' => 200,
                    'newStatValue' => $this->$callable(),
                    'newPrimaryStatPointsValue' => $this->primaryStatPoint
                ];
            }
            else {
                throw new Exception($this->translator->trans("pas_assez_de_points_de_stat_primaire",[], 'app'), 400);
            }
        }
        else if(in_array($statName, self::SECONDARY_STATS)){
            if($this->secondaryStatPoint > 0){
                $this->spendStatPointIncreaseStat($statName);
                $this->secondaryStatPoint--;
                
                $callable = 'get'.ucfirst($statName);
                return [
                    'statusCode' => 200,
                    'newStatValue' => $this->$callable(),
                    'newSecondaryStatPointsValue' => $this->secondaryStatPoint
                ];
            }
            else {
                throw new Exception($this->translator->trans("pas_assez_de_points_de_stat_secondaire", [], 'app'), 400);
            }
        }
        else {
            throw new Exception("statName not found in PRIMARY_STATS or SECONDARY_STATS", 500);
        }
    }

    /**
     * Increase the stat asked by one state
     */
    private function spendStatPointIncreaseStat($statName): void
    {
        $translatedStat = $this->translator->trans($statName, [], 'app');

        if($statName === 'vitality'){
            if($this->$statName === (self::SPEND_STAT_POINT_MAX_PER_LEVEL_HP * $this->level)){
                throw new Exception($this->translator->trans('deja_au_max_pour_votre_niveau', ['%statName%' => ucfirst($translatedStat)], 'app'), 400);
            }
            else {
                $this->$statName += self::SPEND_STAT_POINT_INCREASE_AMOUNT_HP;
            }
        }
        else {
            if($this->$statName === (self::SPEND_STAT_POINT_MAX_PER_LEVEL * $this->level)){
                throw new Exception($this->translator->trans('deja_au_max_pour_votre_niveau', ['%statName%' => ucfirst($translatedStat)], 'app'), 400);
            }
            else {
                $this->$statName += self::SPEND_STAT_POINT_INCREASE_AMOUNT;
            }
        }
    }
}
