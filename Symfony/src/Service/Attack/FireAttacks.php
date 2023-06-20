<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FireAttacks
{
    use AbstractAttacks;

    /** @var Attack Enhardir */
    public Attack $ATTACK_FIRE_ONE;

    /** @var Attack Flammèche */
    public Attack $ATTACK_FIRE_TWO;

    /** @var Attack Poing Feu */
    public Attack $ATTACK_FIRE_THREE;
 
    /** @var Attack Danse du Feu */
    public Attack $ATTACK_FIRE_FOUR;

    public function loadFireAttacks() {
        $this->ATTACK_FIRE_ONE = $this->attackRepository->find('ATTACK_FIRE_ONE');
        $this->ATTACK_FIRE_TWO = $this->attackRepository->find('ATTACK_FIRE_TWO');
        $this->ATTACK_FIRE_THREE = $this->attackRepository->find('ATTACK_FIRE_THREE');
        $this->ATTACK_FIRE_FOUR = $this->attackRepository->find('ATTACK_FIRE_FOUR');
    }

    /**
     * Enhardir : Augmente la force du lanceur.
     */
    public function ATTACK_FIRE_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIRE_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIRE_ONE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FIRE_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FIRE_ONE->getCriticalPower());

        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($strengthBuff);
    }

    /**
     * Flammèche : Inflige des dégâts spéciaux à la cible avec 20% de chance de brûler celle-ci.
     */
    public function ATTACK_FIRE_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIRE_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIRE_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_FIRE_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_FIRE_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du control & Application - */
        if($this->doInflictDamagingStatus(20, $caster->getPresence(), $target->getImpassiveness())){
            $damagingStatus = new DamagingStatus(DamagingStatus::DAMAGING_BURN, $caster->getPresence(), $this::$combatLog);

            $target->addStatus($damagingStatus);
        }
    }

    /**
     * Poing Feu : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_FIRE_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIRE_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIRE_THREE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FIRE_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FIRE_THREE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Danse du Feu : Inflige des dégâts mixtes à la cible.
     */
    public function ATTACK_FIRE_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FIRE_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FIRE_FOUR, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FIRE_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FIRE_FOUR->getCriticalPower());

        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_FIRE_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_FIRE_FOUR->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);
    }
}