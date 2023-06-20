<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait SteelAttacks
{
    use AbstractAttacks;

    /** @var Attack Analyse */
    public Attack $ATTACK_STEEL_ONE;

    /** @var Attack Lancécrou */
    public Attack $ATTACK_STEEL_TWO;

    /** @var Attack Griffes Acier */
    public Attack $ATTACK_STEEL_THREE;
 
    /** @var Attack Bombe Aimant */
    public Attack $ATTACK_STEEL_FOUR;

    public function loadSteelAttacks() {
        $this->ATTACK_STEEL_ONE = $this->attackRepository->find('ATTACK_STEEL_ONE');
        $this->ATTACK_STEEL_TWO = $this->attackRepository->find('ATTACK_STEEL_TWO');
        $this->ATTACK_STEEL_THREE = $this->attackRepository->find('ATTACK_STEEL_THREE');
        $this->ATTACK_STEEL_FOUR = $this->attackRepository->find('ATTACK_STEEL_FOUR');
    }

    /**
     * Analyse : Augmente la Coordination de l'équipe alliée (y compris soi-même) pendant 3 tours.
     */
    public function ATTACK_STEEL_ONE(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_STEEL_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_STEEL_ONE, $doCritical);
        
        /* - Calcul des buffs & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_STEEL_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_STEEL_ONE->getCriticalPower());
        
        foreach ($targets as $target) {
            $buffStatus = new StatisticModifierStatus(3, 'coordination', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

            $target->addStatus($buffStatus);
        }
    }

    /**
     * Lancécrou : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_STEEL_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_STEEL_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_STEEL_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_STEEL_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_STEEL_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);
    }

    /**
     * Griffes Acier : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_STEEL_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_STEEL_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_STEEL_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_STEEL_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_STEEL_THREE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Bombe Aimant : Inflige des dégâts physique à tout les adversaires.
     */
    public function ATTACK_STEEL_FOUR(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_STEEL_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_STEEL_FOUR, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_STEEL_FOUR->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_STEEL_FOUR->getCriticalPower());

            $target->receiveDamage($totalRawPhysicalDamage, 0);
        }
    }
}