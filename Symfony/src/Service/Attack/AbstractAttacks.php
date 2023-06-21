<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Repository\AttackRepository;
use App\Service\Combat\Arena;
use App\Service\Combat\Fighter;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

trait AbstractAttacks
{
    public AttackRepository $attackRepository;
    public static CombatLog $combatLog;
    public static Arena $arena;

    /**
     * Equivalent of __construct()
     */
    public function loadAbstractAttacks() {
        $this->attackRepository = &static::$arena->attackRepository;
        $this::$combatLog = &static::$combatLog;
    }

    /**
     * Calculate the raw damage for a physical Attack.
     */
    public static function calculatePhysicalDamage(float $attackPower, int $casterStrength): int
    {
        return round($attackPower * ($casterStrength / 5) * 1.25);
    }

    /**
     * Calculate the damage received from a physical Attack.
     */
    public static function calculatePhysicalDamageReceived(int $rawDamage, int $targetLevel, int $targetStamina): int
    {
        return round($rawDamage / (($targetLevel / 2) + ($targetStamina / 8)));
    }

    /**
     * Calculate the raw damage for a special Attack.
     */
    public static function calculateSpecialDamage(float $attackPower, int $casterPower): int
    {
        return round($attackPower * ($casterPower / 5) * 1.25);
    }

    /**
     * Calculate the damage received from a special Attack.
     */
    public static function calculateSpecialDamageReceived(int $rawDamage, int $targetLevel, int $targetBravery) : int
    {
        return round($rawDamage / (($targetLevel / 2) + ($targetBravery / 8)));
    }

    /**
     * Calculate the raw healing done by an Attack.
     */
    public static function calculateHealing(float $healingPower, int $casterPresence): int
    {
        return round($healingPower * ($casterPresence / 5) * 0.8);
    }

    /**
     * Calculate the value of a buff inflicted to a character by an Attack.
     */
    public static function calculateBuffStatusValue(float $statusPower, int $casterPresence): int
    {
        return round($statusPower * ($casterPresence / 5) * 0.8);
    }
    
    /**
     * Calculate the value of a nerf inflicted to a character by an Attack.
     */
    public static function calculateRawNerfStatusValue(float $statusPower, int $casterPresence): int
    {
        return round($statusPower * ($casterPresence / 5) * 1.25);
    }

    /**
     * Calculate the value of a nerf received from an Attack.
     */
    public static function calculateNerfValueReceived(int $rawNerfStatusValue, int $targetLevel, int $targetImpassiveness): int
    {
        return round($rawNerfStatusValue / (($targetLevel / 1.5) + ($targetImpassiveness / 8)));
    }

    /**
     * Calculate the value of a Damaging Status.
     */
    public static function calculateDamagingStatusValue(float $damagingStatusPower, int $casterPresence, int $targetImpassiveness, int $targetLevel): int
    {
        $rawValue = round($damagingStatusPower * ($casterPresence / 5) * 1.25);

        return round($rawValue / (($targetLevel / 1.5) + ($targetImpassiveness / 8)));
    }

    /**
     * Returns true if the control status is inflicted
     */
    public static function doInflictControlStatus(float $controlStatusPower, int $casterPresence, int $targetImpassiveness, int $numberOfTargetCurrentControlStatus): bool
    {
        $applyControlStatusChance = ($controlStatusPower * $casterPresence) / ($targetImpassiveness * (1 + $numberOfTargetCurrentControlStatus));
        $diceResult = rand(1, 100);

        if($diceResult <= $applyControlStatusChance){
            return true;
        }
        
        return false;
    }

    /**
     * Returns true if the damaging status is inflicted
     */
    public static function doInflictDamagingStatus(float $controlStatusPower, int $casterPresence, int $targetImpassiveness): bool
    {
        $applyControlStatusChance = ($controlStatusPower * $casterPresence) / $targetImpassiveness;
        $diceResult = rand(1, 100);

        if($diceResult <= $applyControlStatusChance){
            return true;
        }
        
        return false;
    }

    /**
     * Returns true if the Attack deals a critical.
     */
    public static function doCritical(int $casterCoordination, int $targetLevel, ?int $targetAgility): bool
    {
        if($targetAgility === null){
            $criticalChance = $casterCoordination * 2 / ($targetLevel / 1.75);
        }
        else {
            $criticalChance = $casterCoordination * 2 / (($targetLevel + $targetAgility) / 2.75);
        }

        if($criticalChance > 50){
            $criticalChance = 50;
        }
        
        $diceResult = rand(1, 100);

        if($diceResult <= $criticalChance){
            return true;
        }

        return false;
    }

    /**
     * Returns true if the caster deals a STAB.
     */
    public static function doStab(Fighter $caster, Attack $attack)
    {
        $hasStab = false;

        foreach ($caster->getTypes() as $type) {
            if($type->getName() === $attack->getType()->getName()){
                $hasStab = true;
            }
        }

        return $hasStab;
    }

    /**
     * Returns the value after Stab and Critical applied, if necessary.
     */
    public static function calculateValueAfterStabAndCritical(int $value, bool $doCritical, bool $doStab, int $criticalPower): int
    {
        if($doStab){
            $value = $value * FighterAttacks::STAB_DAMAGE;
        }

        if($doCritical){
            $valuePercent = $value / 100;
            $value = round($value + ($valuePercent * $criticalPower));

        }

        return $value;
    }

    /**
     * Returns true if the Target dodge the Attack.
     */
    public static function doDodge(int $casterCoordination, int $casterLevel, int $targetAgility): bool
    {
        $dodgeChance = $targetAgility * 2 / (($casterLevel + $casterCoordination) / 2.75);
        
        if($dodgeChance > 50){
            $dodgeChance = 50;
        }

        $diceResult = rand(1, 100);

        if($diceResult <= $dodgeChance){
            return true;
        }

        return false;
    }

    /**
     * Returns the average level of a group of fighter
     */
    public static function averageFighterLevel(array $fighters)
    {
        $totalLevel = 0;

        foreach ($fighters as $fighter) {
            $totalLevel += $fighter->getLevel();
        }

        return round($totalLevel / count($fighters));
    }
}