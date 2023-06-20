<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait WaterAttacks
{
    use AbstractAttacks;

    /** @var Attack Absorb'O */
    public Attack $ATTACK_WATER_ONE;

    /** @var Attack Pistolet à O */
    public Attack $ATTACK_WATER_TWO;

    /** @var Attack Aqua-Jet */
    public Attack $ATTACK_WATER_THREE;
 
    /** @var Attack Parabochaines */
    public Attack $ATTACK_WATER_FOUR;

    public function loadWaterAttacks() {
        $this->ATTACK_WATER_ONE = $this->attackRepository->find('ATTACK_WATER_ONE');
        $this->ATTACK_WATER_TWO = $this->attackRepository->find('ATTACK_WATER_TWO');
        $this->ATTACK_WATER_THREE = $this->attackRepository->find('ATTACK_WATER_THREE');
        $this->ATTACK_WATER_FOUR = $this->attackRepository->find('ATTACK_WATER_FOUR');
    }

    /**
     * Absorb'O : Soigne le lanceur.
     */
    public function ATTACK_WATER_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_WATER_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_WATER_ONE, $doCritical);

        /* - Calcul du soin & Application - */
        $rawHealing = $this->calculateHealing($this->ATTACK_WATER_ONE->getPower(), $caster->getPresence());
        $totalRawHealing = $this->calculateValueAfterStabAndCritical($rawHealing, $doCritical, $doStab, $this->ATTACK_WATER_ONE->getCriticalPower());

        $target->receiveHealing($totalRawHealing);
    }

    /**
     * Pistolet à O : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_WATER_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_WATER_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_WATER_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_WATER_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_WATER_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);
    }

    /**
     * Aqua-Jet : Inflige des dégâts physique à la cible, avec 30% de chance de la rendre confuse.
     */
    public function ATTACK_WATER_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_WATER_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_WATER_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_WATER_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_WATER_THREE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(30, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
    
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Repli : Augmente l'Endurance du lanceur pendant 3 tours.
     */
    public function ATTACK_WATER_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_WATER_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_WATER_FOUR, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_WATER_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_WATER_FOUR->getCriticalPower());

        $staminaBuff = new StatisticModifierStatus(3, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($staminaBuff);
    }
}