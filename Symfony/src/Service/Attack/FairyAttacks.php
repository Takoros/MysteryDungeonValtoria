<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FairyAttacks
{
    use AbstractAttacks;

    /** @var Attack Doux Baiser */
    public Attack $ATTACK_FAIRY_ONE;

    /** @var Attack Voix Enjôleuse */
    public Attack $ATTACK_FAIRY_TWO;

    /** @var Attack Vigilance */
    public Attack $ATTACK_FAIRY_THREE;
 
    /** @var Attack Câlinerie */
    public Attack $ATTACK_FAIRY_FOUR;

    public function loadFairyAttacks() {
        $this->ATTACK_FAIRY_ONE = $this->attackRepository->find('ATTACK_FAIRY_ONE');
        $this->ATTACK_FAIRY_TWO = $this->attackRepository->find('ATTACK_FAIRY_TWO');
        $this->ATTACK_FAIRY_THREE = $this->attackRepository->find('ATTACK_FAIRY_THREE');
        $this->ATTACK_FAIRY_FOUR = $this->attackRepository->find('ATTACK_FAIRY_FOUR');
    }

    /**
     * Doux Baiser : Inflige Confusion à la cible.
     */
    public function ATTACK_FAIRY_ONE(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FAIRY_ONE, false);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(100, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Voix Enjôleuse : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_FAIRY_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FAIRY_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FAIRY_TWO, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_FAIRY_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_FAIRY_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);
    }

    /**
     * Vigilance : Augmente l'impassibilité du lanceur pendant 3 tours.
     */
    public function ATTACK_FAIRY_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FAIRY_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FAIRY_THREE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FAIRY_THREE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FAIRY_THREE->getCriticalPower());

        $impassivenessBuff = new StatisticModifierStatus(3, 'impassiveness', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($impassivenessBuff);
    }

    /**
     * Câlinerie : Inflige des dégâts physique à la cible et réduit sa force pendant 2 tours.
     */
    public function ATTACK_FAIRY_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FAIRY_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FAIRY_FOUR, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }
        
        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FAIRY_FOUR->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FAIRY_FOUR->getCriticalPower());
        
        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_FAIRY_FOUR->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_FAIRY_FOUR->getCriticalPower());
        $nerfStatus = new StatisticModifierStatus(2, 'strength', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }
}