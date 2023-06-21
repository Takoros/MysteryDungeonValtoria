<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait PoisonAttacks
{
    use AbstractAttacks;

    /** @var Attack Toxik */
    public Attack $ATTACK_POISON_ONE;

    /** @var Attack Dard-Venin */
    public Attack $ATTACK_POISON_TWO;

    /** @var Attack Acide */
    public Attack $ATTACK_POISON_THREE;
 
    /** @var Attack Choc Venin */
    public Attack $ATTACK_POISON_FOUR;

    public function loadPoisonAttacks() {
        $this->ATTACK_POISON_ONE = $this->attackRepository->find('ATTACK_POISON_ONE');
        $this->ATTACK_POISON_TWO = $this->attackRepository->find('ATTACK_POISON_TWO');
        $this->ATTACK_POISON_THREE = $this->attackRepository->find('ATTACK_POISON_THREE');
        $this->ATTACK_POISON_FOUR = $this->attackRepository->find('ATTACK_POISON_FOUR');
    }

    /**
     * Toxik : Inflige Poison Grave à la cible.
     */
    public function ATTACK_POISON_ONE(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_POISON_ONE, false);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(100, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $damagingStatus = new DamagingStatus(DamagingStatus::DAMAGING_BAD_POISON, $caster->getPresence(), $this::$combatLog);
            
            $target->addStatus($damagingStatus);
        }
    }

    /**
     * Dard-Venin : Inflige des dégâts physique à la cible, a 30% de chance d'infliger Poison.
     */
    public function ATTACK_POISON_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_POISON_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_POISON_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_POISON_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_POISON_TWO->getCriticalPower());
        
        $target->receiveDamage($totalRawPhysicalDamage, 0);

        if($this->doInflictDamagingStatus(30, $caster->getPresence(), $target->getImpassiveness())){
            $damagingStatus = new DamagingStatus(DamagingStatus::DAMAGING_POISON, $caster->getPresence(), $this::$combatLog);
            
            $target->addStatus($damagingStatus);
        }
    }

    /**
     * Acide : Inflige des dégâts spéciaux à tout les ennemis, de plus, possède 10% de chance de réduire la force de celle-ci pendant 2 tours.
     */
    public function ATTACK_POISON_THREE(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_POISON_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_POISON_THREE, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_POISON_THREE->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_POISON_THREE->getCriticalPower());

            $target->receiveDamage(0, $totalRawSpecialDamage);

            if($this->doInflictDamagingStatus(10, $caster->getPresence(), $target->getImpassiveness())){
                /* - Calcul du nerf & Application - */
                $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_POISON_THREE->getStatusPower(), $caster->getPresence());
                $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_POISON_THREE->getCriticalPower());

                $nerfStatus = new StatisticModifierStatus(2, 'strength', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

                $target->addStatus($nerfStatus);
            }
        }
    }

    /**
     * Choc Venin : Inflige des dégâts spéciaux à la cible, si celle-ci est empoisonnée, infligé également des dégâts physique de puissance 2 en utilisant la Présence comme statistique.
     */
    public function ATTACK_POISON_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_POISON_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_POISON_FOUR, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $totalRawPhysicalDamage = 0;
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_POISON_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_POISON_FOUR->getCriticalPower());

        if($target->isAffectedByDamaging(DamagingStatus::DAMAGING_POISON) || $target->isAffectedByDamaging(DamagingStatus::DAMAGING_BAD_POISON)){
            $rawPhysicalDamage = $this->calculatePhysicalDamage(2, $caster->getPresence());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_POISON_FOUR->getCriticalPower());
        }

        $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);
    }
}