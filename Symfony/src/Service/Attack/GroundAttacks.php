<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait GroundAttacks
{
    use AbstractAttacks;

    /** @var Attack Jet de sable */
    public Attack $ATTACK_GROUND_ONE;

    /** @var Attack Tourbi-Sable */
    public Attack $ATTACK_GROUND_TWO;

    /** @var Attack Tir de boue */
    public Attack $ATTACK_GROUND_THREE;
 
    /** @var Attack Tunnel */
    public Attack $ATTACK_GROUND_FOUR;

    public function loadGroundAttacks() {
        $this->ATTACK_GROUND_ONE = $this->attackRepository->find('ATTACK_GROUND_ONE');
        $this->ATTACK_GROUND_TWO = $this->attackRepository->find('ATTACK_GROUND_TWO');
        $this->ATTACK_GROUND_THREE = $this->attackRepository->find('ATTACK_GROUND_THREE');
        $this->ATTACK_GROUND_FOUR = $this->attackRepository->find('ATTACK_GROUND_FOUR');
    }

    /**
     * Jet de sable : Réduit la Coordination de la cible pendant 2 tours.
     */
    public function ATTACK_GROUND_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GROUND_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
 
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GROUND_ONE, $doCritical);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_GROUND_ONE->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_GROUND_ONE->getCriticalPower());

        $nerfStatus = new StatisticModifierStatus(2, 'coordination', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Tourbi-Sable : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_GROUND_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GROUND_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
 
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GROUND_TWO, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
    
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
    
            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_GROUND_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_GROUND_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Tir de boue : Inflige des dégâts spéciaux à la cible et réduit sa Vitesse pendant 2 tours.
     */
    public function ATTACK_GROUND_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GROUND_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GROUND_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());

        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_GROUND_THREE->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_GROUND_THREE->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_GROUND_THREE->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_GROUND_THREE->getCriticalPower());

        $nerfStatus = new StatisticModifierStatus(2, 'speed', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Tunnel : Attaque en deux tours. Lors du premier tour, augmente son Endurance et son Courage pendant 2 tours. Lors du second tour, inflige des dégâts physique à la cible.
     */
    public function ATTACK_GROUND_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GROUND_FOUR);
        
        /* - Premier passage - */
        if($caster->getNextMultipleStepAttack() === []){
            $this::$combatLog->addStringLog("{$caster->getName()} lance {$this->ATTACK_GROUND_FOUR->getName()} et s'enfouit dans le sol.");
            $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

            /* - Calcul des buff & Application - */
            $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_GROUND_FOUR->getStatusPower(), $caster->getPresence());
            $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_GROUND_FOUR->getCriticalPower());

            $staminaBuff = new StatisticModifierStatus(3, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
            $braveryBuff = new StatisticModifierStatus(3, 'bravery', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

            $caster->addStatus($staminaBuff);
            $caster->addStatus($braveryBuff);

            $caster->setNextMultipleStepAttack(['Attack' => $this->ATTACK_GROUND_FOUR, 'step' => 2]);
        }
        else if($caster->getNextMultipleStepAttack()['step'] === 2){
             /* - Second passage - */
            $this::$combatLog->addStringLog("{$caster->getName()} surgit du sol et frappe {$target->getName()}");
            $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
        
                return ;
            }

            /* - Calcul des dégâts & Application - */
            $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_GROUND_TWO->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_GROUND_TWO->getCriticalPower());

            $target->receiveDamage($totalRawPhysicalDamage, 0);

            $caster->resetNextMultipleStepAttack();
        }
    }
}