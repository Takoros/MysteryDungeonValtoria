<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait BugAttacks
{
    use AbstractAttacks;

    /** @var Attack Chargeur */
    public Attack $ATTACK_BUG_ONE;

    /** @var Attack Éclair */
    public Attack $ATTACK_BUG_TWO;

    /** @var Attack Crocs Éclair */
    public Attack $ATTACK_BUG_THREE;
 
    /** @var Attack Parabochaines */
    public Attack $ATTACK_BUG_FOUR;

    public function loadBugAttacks() {
        $this->ATTACK_BUG_ONE = $this->attackRepository->find('ATTACK_BUG_ONE');
        $this->ATTACK_BUG_TWO = $this->attackRepository->find('ATTACK_BUG_TWO');
        $this->ATTACK_BUG_THREE = $this->attackRepository->find('ATTACK_BUG_THREE');
        $this->ATTACK_BUG_FOUR = $this->attackRepository->find('ATTACK_BUG_FOUR');
    }

    /**
     * Sécrétion : Réduit la Vitesse de la cible.
     */
    public function ATTACK_BUG_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_BUG_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_BUG_ONE, $doCritical);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_BUG_ONE->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_BUG_ONE->getCriticalPower());

        $nerfStatus = new StatisticModifierStatus(2, 'speed', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Piqûre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_BUG_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_BUG_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_BUG_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
    
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
    
            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_BUG_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_BUG_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Survinsecte : Inflige des dégâts spéciaux à tout les ennemis, diminie le pouvoir de ceux-ci.
     */
    public function ATTACK_BUG_THREE(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_BUG_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_BUG_THREE, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_BUG_THREE->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_BUG_THREE->getCriticalPower());

            $target->receiveDamage(0, $totalRawSpecialDamage);

            /* - Calcul du nerf & Application - */
            $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_BUG_THREE->getStatusPower(), $caster->getPresence());
            $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_BUG_THREE->getCriticalPower());

            $nerfStatus = new StatisticModifierStatus(2, 'power', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

            $target->addStatus($nerfStatus);
        }
    }

    /**
     * Papillodanse : Augmente le Pouvoir, le Courage et la Vitesse du lanceur pendant 3 tours.
     */
    public function ATTACK_BUG_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_BUG_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_BUG_FOUR, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_BUG_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_BUG_FOUR->getCriticalPower());

        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $braveryBuff = new StatisticModifierStatus(3, 'bravery', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $speedBuff = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($powerBuff);
        $target->addStatus($braveryBuff);
        $target->addStatus($speedBuff);
    }
}