<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait ElectricAttacks
{
    use AbstractAttacks;

    /** @var Attack Chargeur */
    public Attack $ATTACK_ELECTRIC_ONE;

    /** @var Attack Éclair */
    public Attack $ATTACK_ELECTRIC_TWO;

    /** @var Attack Crocs Éclair */
    public Attack $ATTACK_ELECTRIC_THREE;
 
    /** @var Attack Parabochaines */
    public Attack $ATTACK_ELECTRIC_FOUR;

    public function loadElectricAttacks() {
        $this->ATTACK_ELECTRIC_ONE = $this->attackRepository->find('ATTACK_ELECTRIC_ONE');
        $this->ATTACK_ELECTRIC_TWO = $this->attackRepository->find('ATTACK_ELECTRIC_TWO');
        $this->ATTACK_ELECTRIC_THREE = $this->attackRepository->find('ATTACK_ELECTRIC_THREE');
        $this->ATTACK_ELECTRIC_FOUR = $this->attackRepository->find('ATTACK_ELECTRIC_FOUR');
    }

    /**
     * Chargeur : Augmente la vitesse et l'agilité du lanceur pendant 3 tours.
     */
    public function ATTACK_ELECTRIC_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ELECTRIC_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ELECTRIC_ONE, $doCritical);

        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_ELECTRIC_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_ELECTRIC_ONE->getCriticalPower());

        $speedBuff = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $agilityBuff = new StatisticModifierStatus(3, 'agility', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($speedBuff);
        $target->addStatus($agilityBuff);
    }

    /**
     * Éclair : Inflige des dégâts spéciaux à la cible, avec 30% de chance de paralyser la cible.
     */
    public function ATTACK_ELECTRIC_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ELECTRIC_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ELECTRIC_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_ELECTRIC_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doStab, $doCritical, $this->ATTACK_ELECTRIC_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(30, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_PARALYSIS, $this::$combatLog);
    
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Crocs Éclair : Inflige des dégâts physique à la cible et réduit son endurance.
     */
    public function ATTACK_ELECTRIC_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ELECTRIC_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ELECTRIC_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_ELECTRIC_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doStab, $doCritical, $this->ATTACK_ELECTRIC_THREE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_ELECTRIC_THREE->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doStab, $doCritical, $this->ATTACK_ELECTRIC_THREE->getCriticalPower());

        $nerfStatus = new StatisticModifierStatus(2, 'stamina', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Parabochaines : Inflige des dégâts spéciaux, réduit la vitesse, l'agilité et la coordination de la cible pendant 3 tours.
     */
    public function ATTACK_ELECTRIC_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ELECTRIC_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ELECTRIC_FOUR, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_ELECTRIC_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doStab, $doCritical, $this->ATTACK_ELECTRIC_FOUR->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_ELECTRIC_FOUR->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doStab, $doCritical, $this->ATTACK_ELECTRIC_FOUR->getCriticalPower());

        $nerfSpeedStatus = new StatisticModifierStatus(3, 'speed', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);
        $nerfAgilityStatus = new StatisticModifierStatus(3, 'agility', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);
        $nerfCoordinationStatus = new StatisticModifierStatus(3, 'coordination', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfSpeedStatus);
        $target->addStatus($nerfAgilityStatus);
        $target->addStatus($nerfCoordinationStatus);
    }
}