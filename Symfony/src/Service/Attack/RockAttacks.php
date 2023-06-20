<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait RockAttacks
{
    use AbstractAttacks;

    /** @var Attack Renforcement */
    public Attack $ATTACK_ROCK_ONE;

    /** @var Attack Jet Pierre */
    public Attack $ATTACK_ROCK_TWO;

    /** @var Attack Assourdir */
    public Attack $ATTACK_ROCK_THREE;
 
    /** @var Attack Lourdeur */
    public Attack $ATTACK_ROCK_FOUR;

    public function loadRockAttacks() {
        $this->ATTACK_ROCK_ONE = $this->attackRepository->find('ATTACK_ROCK_ONE');
        $this->ATTACK_ROCK_TWO = $this->attackRepository->find('ATTACK_ROCK_TWO');
        $this->ATTACK_ROCK_THREE = $this->attackRepository->find('ATTACK_ROCK_THREE');
        $this->ATTACK_ROCK_FOUR = $this->attackRepository->find('ATTACK_ROCK_FOUR');
    }

    /**
     * Renforcement : Augmente l'endurance du lanceur pendant 3 tours.
     */
    public function ATTACK_ROCK_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ROCK_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ROCK_ONE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_ROCK_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_ROCK_ONE->getCriticalPower());

        $staminaBuff = new StatisticModifierStatus(3, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($staminaBuff);
    }

    /**
     * Jet Pierre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_ROCK_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ROCK_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ROCK_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_ROCK_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_ROCK_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Assourdir : Inflige des dégâts spéciaux à la cible, avec 15% de chance de la rendre confuse.
     */
    public function ATTACK_ROCK_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ROCK_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ROCK_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_ROCK_THREE->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_ROCK_THREE->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(15, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
    
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Lourdeur : Inflige des dégâts physique et réduit la force ainsi que la coordination de la cible pendant 2 tours.
     */
    public function ATTACK_ROCK_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ROCK_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ROCK_FOUR, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_ROCK_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_ROCK_FOUR->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_ROCK_FOUR->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_ROCK_FOUR->getCriticalPower());

        $nerfStrengthStatus = new StatisticModifierStatus(2, 'strength', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);
        $nerfCoordinationStatus = new StatisticModifierStatus(2, 'coordination', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStrengthStatus);
        $target->addStatus($nerfCoordinationStatus);
    }
}