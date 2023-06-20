<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait DragonAttacks
{
    use AbstractAttacks;

    /** @var Attack Danse Draco */
    public Attack $ATTACK_DRAGON_ONE;

    /** @var Attack Ouragan */
    public Attack $ATTACK_DRAGON_TWO;

    /** @var Attack Abattage */
    public Attack $ATTACK_DRAGON_THREE;
 
    /** @var Attack Dracacophonie */
    public Attack $ATTACK_DRAGON_FOUR;

    public function loadDragonAttacks() {
        $this->ATTACK_DRAGON_ONE = $this->attackRepository->find('ATTACK_DRAGON_ONE');
        $this->ATTACK_DRAGON_TWO = $this->attackRepository->find('ATTACK_DRAGON_TWO');
        $this->ATTACK_DRAGON_THREE = $this->attackRepository->find('ATTACK_DRAGON_THREE');
        $this->ATTACK_DRAGON_FOUR = $this->attackRepository->find('ATTACK_DRAGON_FOUR');
    }

    /**
     * Danse Draco : Augmente la vitesse et la force du lanceur
     */
    public function ATTACK_DRAGON_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DRAGON_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DRAGON_ONE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_DRAGON_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_DRAGON_ONE->getCriticalPower());

        $speedBuff = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($speedBuff);
        $target->addStatus($strengthBuff);
    }

    /**
     * Ouragan : Inflige des dégâts spéciaux à tout les ennemis.
     */
    public function ATTACK_DRAGON_TWO(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DRAGON_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DRAGON_TWO, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_DRAGON_TWO->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_DRAGON_TWO->getCriticalPower());

            $target->receiveDamage(0, $totalRawSpecialDamage);
        }
    }

    /**
     * Abattage : Inflige des dégâts physiques à tout les ennemis et réduit leur pouvoir pendant 2 tours.
     */
    public function ATTACK_DRAGON_THREE(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DRAGON_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DRAGON_THREE, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_DRAGON_THREE->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_DRAGON_THREE->getCriticalPower());

            $target->receiveDamage($totalRawPhysicalDamage, 0);

            /* - Calcul du nerf & Application - */
            $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_DRAGON_THREE->getStatusPower(), $caster->getPresence());
            $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_DRAGON_THREE->getCriticalPower());

            $nerfStatus = new StatisticModifierStatus(2, 'power', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

            $target->addStatus($nerfStatus);
        }
    }

    /**
     * Dracacophonie : S'auto inflige des dégâts mixtes, mais augmente sa force, son pouvoir, sa vitesse et sa coordination pendant 3 tours.
     */
    public function ATTACK_DRAGON_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DRAGON_FOUR);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DRAGON_FOUR, false);

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_DRAGON_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, false, $doStab, $this->ATTACK_DRAGON_FOUR->getCriticalPower());

        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_DRAGON_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, false, $doStab, $this->ATTACK_DRAGON_FOUR->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_DRAGON_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, false, $doStab, $this->ATTACK_DRAGON_FOUR->getCriticalPower());

        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $speedBuff = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $coordinationBuff = new StatisticModifierStatus(3, 'coordination', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($strengthBuff);
        $target->addStatus($powerBuff);
        $target->addStatus($speedBuff);
        $target->addStatus($coordinationBuff);
    }
}