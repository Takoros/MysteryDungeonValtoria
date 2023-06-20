<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait PsyAttacks
{
    use AbstractAttacks;

    /** @var Attack Hypnose */
    public Attack $ATTACK_PSY_ONE;

    /** @var Attack Choc Mental */
    public Attack $ATTACK_PSY_TWO;

    /** @var Attack Psykoud'Boul */
    public Attack $ATTACK_PSY_THREE;
 
    /** @var Attack Vibra Soin */
    public Attack $ATTACK_PSY_FOUR;

    public function loadPsyAttacks() {
        $this->ATTACK_PSY_ONE = $this->attackRepository->find('ATTACK_PSY_ONE');
        $this->ATTACK_PSY_TWO = $this->attackRepository->find('ATTACK_PSY_TWO');
        $this->ATTACK_PSY_THREE = $this->attackRepository->find('ATTACK_PSY_THREE');
        $this->ATTACK_PSY_FOUR = $this->attackRepository->find('ATTACK_PSY_FOUR');
    }

    /**
     * Hypnose : Inflige sommeil à la cible
     */
    public function ATTACK_PSY_ONE(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_PSY_ONE, false);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(100, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_SLEEP, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Choc Mental : Inflige des dégâts spéciaux à la cible et possède 10% de chance d'infliger confusion à celle-ci.
     */
    public function ATTACK_PSY_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_PSY_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_PSY_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_PSY_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_PSY_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(10, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Psykoud'Boul : Inflige des dégâts physique à la cible
     */
    public function ATTACK_PSY_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_PSY_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_PSY_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_PSY_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_PSY_THREE->getCriticalPower());
        
        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Vibra Soin : Soigne tout les alliés du groupe
     */
    public function ATTACK_PSY_FOUR(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_PSY_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_PSY_FOUR, $doCritical);

        /* - Calcul du soin & Application - */
        $rawHealing = $this->calculateHealing($this->ATTACK_PSY_FOUR->getPower(), $caster->getPresence());
        $totalRawHealing = $this->calculateValueAfterStabAndCritical($rawHealing, $doCritical, $doStab, $this->ATTACK_PSY_FOUR->getCriticalPower());

        foreach ($targets as $target) {
            $target->receiveHealing($totalRawHealing);
        }
    }
}