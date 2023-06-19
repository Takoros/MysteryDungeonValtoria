<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FightingAttacks
{
    use AbstractAttacks;

    /** @var Attack Harmonie */
    public Attack $ATTACK_FIGHTING_ONE;

    /** @var Attack Poing Karaté */
    public Attack $ATTACK_FIGHTING_TWO;

    /** @var Attack Aurasphère */
    public Attack $ATTACK_FIGHTING_THREE;
 
    /** @var Attack Échauffement */
    public Attack $ATTACK_FIGHTING_FOUR;

    public function loadFightingAttacks() {
        $this->ATTACK_FIGHTING_ONE = $this->attackRepository->find('ATTACK_FIGHTING_ONE');
        $this->ATTACK_FIGHTING_TWO = $this->attackRepository->find('ATTACK_FIGHTING_TWO');
        $this->ATTACK_FIGHTING_THREE = $this->attackRepository->find('ATTACK_FIGHTING_THREE');
        $this->ATTACK_FIGHTING_FOUR = $this->attackRepository->find('ATTACK_FIGHTING_FOUR');
    }

    /**
     * Harmonie : Augmente la Présence et l'Impassibilité du lanceur pendant 3 tours.
     */
    public function ATTACK_FIGHTING_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIGHTING_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIGHTING_ONE, $doCritical);
 
        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FIGHTING_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FIGHTING_ONE->getCriticalPower());

        $presenceBuff = new StatisticModifierStatus(3, 'presence', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $impassivenessBuff = new StatisticModifierStatus(3, 'impassiveness', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($presenceBuff);
        $caster->addStatus($impassivenessBuff);
    }

    /**
     * Poing Karaté :Inflige des dégâts physique à la cible.
     */
    public function ATTACK_FIGHTING_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIGHTING_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIGHTING_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }
        
        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FIGHTING_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FIGHTING_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Aurasphère : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_FIGHTING_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIGHTING_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIGHTING_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_FIGHTING_THREE->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_FIGHTING_THREE->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);
    }

    /**
     * Échauffement : Inflige des dégâts physique à la cible et augmente la force ainsi que le pouvoir du lanceur pendant 3 tours.
     */
    public function ATTACK_FIGHTING_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIGHTING_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIGHTING_FOUR, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }
        
        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FIGHTING_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FIGHTING_FOUR->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FIGHTING_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FIGHTING_FOUR->getCriticalPower());

        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($strengthBuff);
        $caster->addStatus($powerBuff);
    }
}