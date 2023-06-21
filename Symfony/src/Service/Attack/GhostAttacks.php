<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;

trait GhostAttacks
{
    use AbstractAttacks;

    /** @var Attack Onde Folie */
    public Attack $ATTACK_GHOST_ONE;

    /** @var Attack Étonnement */
    public Attack $ATTACK_GHOST_TWO;

    /** @var Attack Ball'Ombre */
    public Attack $ATTACK_GHOST_THREE;
 
    /** @var Attack Châtiment */
    public Attack $ATTACK_GHOST_FOUR;

    public function loadGhostAttacks() {
        $this->ATTACK_GHOST_ONE = $this->attackRepository->find('ATTACK_GHOST_ONE');
        $this->ATTACK_GHOST_TWO = $this->attackRepository->find('ATTACK_GHOST_TWO');
        $this->ATTACK_GHOST_THREE = $this->attackRepository->find('ATTACK_GHOST_THREE');
        $this->ATTACK_GHOST_FOUR = $this->attackRepository->find('ATTACK_GHOST_FOUR');
    }

    /**
     * Onde Folie : Inflige Confusion à la cible.
     */
    public function ATTACK_GHOST_ONE(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GHOST_ONE, false);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(100, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Étonnement : Inflige des dégâts spéciaux à la cible, 30% de chance d'infliger Pétrification.
     */
    public function ATTACK_GHOST_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GHOST_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GHOST_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_GHOST_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_GHOST_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(30, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_PETRIFICATION, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Ball'Ombre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_GHOST_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GHOST_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GHOST_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_GHOST_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_GHOST_THREE->getCriticalPower());
        
        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Châtiment : Inflige des dégâts spéciaux à la cible, augmente la puissance de l'attaque à 10, si la cible est affectée par un status de contrôle.
     */
    public function ATTACK_GHOST_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GHOST_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GHOST_FOUR, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul Puissance de l'attaque - */

        if($target->getNumberOfCurrentControlStatus() > 0){
            $totalAttackPower = 10;
        }
        else {
            $totalAttackPower = $this->ATTACK_GHOST_FOUR->getPower();
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($totalAttackPower, $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_GHOST_FOUR->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);
    }
}