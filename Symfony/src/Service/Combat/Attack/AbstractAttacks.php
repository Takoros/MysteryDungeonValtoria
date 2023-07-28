<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Repository\AttackRepository;
use App\Service\Combat\Arena;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

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
     * Returns null if the Scope of the attack isnt made for agility based critical chance
     */
    public function calculateTargetAgilityForCriticalCalculation(Attack $Attack, mixed $target): ?int
    {
        if($Attack->getScope() === FighterAttacks::ATTACK_SCOPE_SELF ||
           $Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER ||
           $Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER_LOWEST_VITALITY ||
           $Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ALLY_TEAM ||
           $Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ENEMY_TEAM){

           return null;
        }

        return $target->getAgility();
    }

    /**
     * Returns true if the target has dodged the attack
     */
    public function hasDodged(Fighter $caster, Fighter $target){
        $hasDodged = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($hasDodged){
            $this::$combatLog->addDodgeLog($target);
        }

        return $hasDodged;
    }

    /**
     * Prepares isStab, isCritical and hasDodged, and creates AttackLog plus DodgeLog if necessary
     */
    public function makeAttack(Attack $Attack, Fighter $caster, $target, bool $isDodgeable): object
    {
        $targetAgility = $this->calculateTargetAgilityForCriticalCalculation($Attack, $target);

        /* - Stabs & Critique - */
        $isStab = $this->doStab($caster, $Attack);
        
        if($Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ALLY_TEAM || $Attack->getScope() === FighterAttacks::ATTACK_SCOPE_ENEMY_TEAM){
            $isCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($target), $targetAgility);
        }
        else {
            $isCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $targetAgility);
        }

        $this::$combatLog->addAttackLog($caster, $Attack, $isCritical);

        /* - Esquive - */
        if($isDodgeable){-
            $hasDodged = $this->hasDodged($caster, $target);
        }
        else {
            $hasDodged = false;
        }

        return (object) [
            'isStab' => $isStab,
            'isCritical' => $isCritical,
            'hasDodged' => $hasDodged
        ];
    }

    /**
     * Makes an Attack deal damage and returns damage taken by the target
     */
    public function dealDamage(Attack $Attack, object $attackDetails, string $damageType, Fighter $caster, Fighter $target): int
    {
        /* - Calcul des dégâts & Application - */
        if($damageType === FighterAttacks::DAMAGE_TYPE_PHYSICAL){
            $rawPhysicalDamage = $this->calculatePhysicalDamage($Attack->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
           
            return $target->receiveDamage($totalRawPhysicalDamage, 0);
        }
        else if ($damageType === FighterAttacks::DAMAGE_TYPE_SPECIAL){
            $rawSpecialDamage = $this->calculateSpecialDamage($Attack->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
            
            return $target->receiveDamage(0, $totalRawSpecialDamage);
        }
        else {
            $rawPhysicalDamage = $this->calculatePhysicalDamage($Attack->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
            
            $rawSpecialDamage = $this->calculateSpecialDamage($Attack->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
            
            return $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);
        }
    }

    /**
     * Makes an Attack inflict a Statistic Modifier
     */
    public function inflictStatisticModifier(Attack $Attack, object $attackDetails, string $modifierType, array $statisticsToModify, int $remainingTurns, Fighter $caster, Fighter $target ): void
    {
        foreach ($statisticsToModify as  $statistic) {
            if($modifierType === StatusInterface::TYPE_BUFF){
                $rawBuffAmount = $this->calculateBuffStatusValue($Attack->getStatusPower(), $caster->getPresence());
                $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
                
                $buffStatus = new StatisticModifierStatus($statistic, StatusInterface::TYPE_BUFF, $remainingTurns, $totalBuffAmount, $this::$combatLog);

                $target->addStatus($buffStatus);
            }
            else {
                $rawNerfAmount = $this->calculateRawNerfStatusValue($Attack->getStatusPower(), $caster->getPresence());
                $totalNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());
                
                $nerfStatus = new StatisticModifierStatus($statistic, StatusInterface::TYPE_NERF, $remainingTurns, $totalNerfAmount, $this::$combatLog);
                
                $target->addStatus($nerfStatus);
            }
        }
    }

    /**
     * Makes an Attack inflict a Control Status
     */
    public function inflictControlStatus(int $controlPower, string $controlType, Fighter $caster, Fighter $target): void
    {
        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus($controlPower, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus($controlType, $this::$combatLog);

            $target->addStatus($controlStatus);
        }
    }

    /**
     * Makes an Attack inflict a DamagingStatus
     */
    public function inflictDamagingStatus(int $damagingPower, string $damagingType, Fighter $caster, Fighter $target): void
    {
        if($this->doInflictDamagingStatus($damagingPower, $caster->getPresence(), $target->getImpassiveness())){
            $damagingStatus = new DamagingStatus($damagingType, $caster->getPresence(), $this::$combatLog);

            $target->addStatus($damagingStatus);
        }
    }

    /**
     * Makes an Attack inflict healing
     */
    public function inflictHealing(Attack $Attack, object $attackDetails, Fighter $caster, Fighter $target): void
    {
        /* - Calcul du soin & Application - */
        $rawHealing = $this->calculateHealing($Attack->getPower(), $caster->getPresence());
        $totalRawHealing = $this->calculateValueAfterStabAndCritical($rawHealing, $attackDetails->isCritical, $attackDetails->isStab, $Attack->getCriticalPower());

        $target->receiveHealing($totalRawHealing);
    }

    /* -------------------------------------------------------------------------- */
    /*                              Calculates Values                             */
    /* -------------------------------------------------------------------------- */

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
        return round($healingPower * ($casterPresence / 5) * 0.9);
    }

    /**
     * Calculate the value of a buff inflicted to a character by an Attack.
     */
    public static function calculateBuffStatusValue(float $statusPower, int $casterPresence): int
    {
        return round($statusPower * ($casterPresence / 5) * 0.75);
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
        return round($rawNerfStatusValue / ($targetImpassiveness / 8));
    }

    /**
     * Calculate the value of a Damaging Status.
     */
    public static function calculateDamagingStatusValue(float $damagingStatusPower, int $casterPresence, int $targetImpassiveness, int $targetLevel): int
    {
        $rawValue = round($damagingStatusPower * ($casterPresence / 5) * 1.25);

        return round($rawValue / ($targetImpassiveness / 8));
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
    public static function doInflictDamagingStatus(float $damagingStatusPower, int $casterPresence, int $targetImpassiveness): bool
    {
        $applyControlStatusChance = ($damagingStatusPower * $casterPresence) / $targetImpassiveness;
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