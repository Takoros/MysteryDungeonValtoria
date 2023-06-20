<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait DarkAttacks
{
    use AbstractAttacks;

    /** @var Attack Fumée Protectrice */
    public Attack $ATTACK_DARK_ONE;

    /** @var Attack Morsure */
    public Attack $ATTACK_DARK_TWO;

    /** @var Attack Aboiement */
    public Attack $ATTACK_DARK_THREE;
 
    /** @var Attack Feinte */
    public Attack $ATTACK_DARK_FOUR;

    public function loadDarkAttacks() {
        $this->ATTACK_DARK_ONE = $this->attackRepository->find('ATTACK_DARK_ONE');
        $this->ATTACK_DARK_TWO = $this->attackRepository->find('ATTACK_DARK_TWO');
        $this->ATTACK_DARK_THREE = $this->attackRepository->find('ATTACK_DARK_THREE');
        $this->ATTACK_DARK_FOUR = $this->attackRepository->find('ATTACK_DARK_FOUR');
    }

    /**
     * Fumée Protectrice : Augmente l'Endurance, le Courage et la Vitalité du lanceur pendant 3 tours.
     */
    public function ATTACK_DARK_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DARK_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DARK_ONE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_DARK_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_DARK_ONE->getCriticalPower());

        $staminaBuff = new StatisticModifierStatus(3, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $braveryBuff = new StatisticModifierStatus(3, 'bravery', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $vitalityBuff = new StatisticModifierStatus(3, 'vitality', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($staminaBuff);
        $target->addStatus($braveryBuff);
        $target->addStatus($vitalityBuff);
    }

    /**
     * Morsure : Inflige des dégâts physique à la cible, 30% de chance d'infliger Pétrification.
     */
    public function ATTACK_DARK_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DARK_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DARK_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_DARK_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_DARK_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(30, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_PETRIFICATION, $this::$combatLog);
    
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Aboiement : Inflige des dégâts spéciaux à tout les ennemis, réduisant leur Pouvoir pendant 2 tours.
     */
    public function ATTACK_DARK_THREE(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DARK_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DARK_THREE, $doCritical);

        foreach ($targets as $target) {
            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
            
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

                break;
            }

            /* - Calcul des dégâts & Application - */
            $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_DARK_THREE->getPower(), $caster->getPower());
            $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_DARK_THREE->getCriticalPower());

            $target->receiveDamage(0, $totalRawSpecialDamage);

            /* - Calcul du nerf & Application - */
            $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_DARK_THREE->getStatusPower(), $caster->getPresence());
            $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_DARK_THREE->getCriticalPower());

            $nerfStatus = new StatisticModifierStatus(2, 'power', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

            $target->addStatus($nerfStatus);
        }
    }

    /**
     * Feinte : Inflige des dégâts mixtes à la cible. Ne peut pas être esquivé.
     */
    public function ATTACK_DARK_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_DARK_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_DARK_FOUR, $doCritical);

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_DARK_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_DARK_FOUR->getCriticalPower());

        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_DARK_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_DARK_FOUR->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);
    }
}