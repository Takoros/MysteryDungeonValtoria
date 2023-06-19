<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait NormalAttacks
{
    use AbstractAttacks;

    /** @var Attack Chargeur */
    public Attack $ATTACK_NORMAL_ONE;

    /** @var Attack Éclair */
    public Attack $ATTACK_NORMAL_TWO;

    /** @var Attack Crocs Éclair */
    public Attack $ATTACK_NORMAL_THREE;
 
    /** @var Attack Parabochaines */
    public Attack $ATTACK_NORMAL_FOUR;

    public function loadNormalAttacks() {
        $this->ATTACK_NORMAL_ONE = $this->attackRepository->find('ATTACK_NORMAL_ONE');
        $this->ATTACK_NORMAL_TWO = $this->attackRepository->find('ATTACK_NORMAL_TWO');
        $this->ATTACK_NORMAL_THREE = $this->attackRepository->find('ATTACK_NORMAL_THREE');
        $this->ATTACK_NORMAL_FOUR = $this->attackRepository->find('ATTACK_NORMAL_FOUR');
    }

    /**
     * Flair : Purge les bonus d'agilité de la cible, et augmente la Coordination du lanceur pendant 3 tours.
     */
    public function ATTACK_NORMAL_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_NORMAL_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
 
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_NORMAL_ONE, $doCritical);

        /* - Purge des bonus d'agilité - */

        $target->purgeAllBuffOfStatistic('agility');

        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_NORMAL_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_NORMAL_ONE->getCriticalPower());

        $coordinationBuff = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($coordinationBuff);
    }

    /**
     * Charge : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_NORMAL_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_NORMAL_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_NORMAL_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_NORMAL_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_NORMAL_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Météores : Inflige des dégâts spéciaux à la cible, et augmente le pouvoir du lanceur pendant 3 tours.
     */
    public function ATTACK_NORMAL_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_NORMAL_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_NORMAL_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_NORMAL_THREE->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_NORMAL_THREE->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_NORMAL_THREE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_NORMAL_THREE->getCriticalPower());

        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($powerBuff);
    }

    /**
     * Abri : Augmente l'Endurance et le Courage du lanceur pendant 2 tours.
     */
    public function ATTACK_NORMAL_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_NORMAL_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_NORMAL_FOUR, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_NORMAL_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_NORMAL_FOUR->getCriticalPower());

        $staminaBuff = new StatisticModifierStatus(2, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $braveryBuff = new StatisticModifierStatus(2, 'bravery', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($staminaBuff);
        $target->addStatus($braveryBuff);
    }
}