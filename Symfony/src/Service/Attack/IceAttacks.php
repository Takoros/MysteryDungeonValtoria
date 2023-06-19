<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait IceAttacks
{
    use AbstractAttacks;

    /** @var Attack Buée Noire */
    public Attack $ATTACK_ICE_ONE;

    /** @var Attack Poudreuse */
    public Attack $ATTACK_ICE_TWO;

    /** @var Attack Éclats Glace */
    public Attack $ATTACK_ICE_THREE;
 
    /** @var Attack Onde Boréale */
    public Attack $ATTACK_ICE_FOUR;

    public function loadIceAttacks() {
        $this->ATTACK_ICE_ONE = $this->attackRepository->find('ATTACK_ICE_ONE');
        $this->ATTACK_ICE_TWO = $this->attackRepository->find('ATTACK_ICE_TWO');
        $this->ATTACK_ICE_THREE = $this->attackRepository->find('ATTACK_ICE_THREE');
        $this->ATTACK_ICE_FOUR = $this->attackRepository->find('ATTACK_ICE_FOUR');
    }

    /**
     * Buée Noire : Retire 1 à 3 bonus sur la cible (1 : 45%, 2 : 35%, 3 : 20%).
     */
    public function ATTACK_ICE_ONE(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ICE_ONE, false);
        
        /* - Calcul du nombre d'effet positifs subis - */
        $targetPositiveStatus = $target->getAllPositiveStatus();

        if(count($targetPositiveStatus) < 1){
            $this::$combatLog->addStringLog("Cela n'a aucun effet.");
            return ;
        }

        /* - Calcul du nombre d'effets positifs retirés - */
        $diceResult = rand(1, 100);

        if($diceResult >= 80){
            $numberOfPositiveStatusPurged = 3;
        }
        else if($diceResult >= 45){
            $numberOfPositiveStatusPurged = 2;
        }
        else {
            $numberOfPositiveStatusPurged = 1;
        }

        if($numberOfPositiveStatusPurged > count($targetPositiveStatus)){
            $numberOfPositiveStatusPurged = count($targetPositiveStatus);
        }
        
        /* - Purge des effets positifs - */
        for ($i=0; $i < $numberOfPositiveStatusPurged; $i++) { 
            $targetPositiveStatus[$i]->getPurged($target);
        }

        $target->removePurgedStatus();
    }

    /**
     * Poudreuse : Inflige des dégâts spéciaux à la cible, et réduit son endurance pendant 2 tours.
     */
    public function ATTACK_ICE_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ICE_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ICE_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_ICE_TWO->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_ICE_TWO->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul des nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_EXPLORER_THREE->getStatusPower(), $caster->getPresence());
        $totalNerfAmount =  $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_EXPLORER_THREE->getCriticalPower());
        
        $nerfStatus =  new StatisticModifierStatus(2, 'stamina', $totalNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Éclats Glace : Inflige des dégâts physique à la cible, et augmente la vitesse du lanceur pendant 3 tours.
     */
    public function ATTACK_ICE_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ICE_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
    
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ICE_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_ICE_THREE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_ICE_THREE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_ICE_THREE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_ICE_THREE->getCriticalPower());

        $buffStatus = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $caster->addStatus($buffStatus);
    }

    /**
     * Onde Boréale : Inflige des dégâts spéciaux à la cible, et réduit sa vitesse pendant 2 tours.
     */
    public function ATTACK_ICE_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_ICE_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_ICE_FOUR, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_ICE_FOUR->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_ICE_FOUR->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul des nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_EXPLORER_FOUR->getStatusPower(), $caster->getPresence());
        $totalNerfAmount =  $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_EXPLORER_FOUR->getCriticalPower());
        
        $nerfStatus =  new StatisticModifierStatus(2, 'speed', $totalNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }
}