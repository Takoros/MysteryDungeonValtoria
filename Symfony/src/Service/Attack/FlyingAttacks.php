<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FlyingAttacks
{
    use AbstractAttacks;

    /** @var Attack Danse Plume */
    public Attack $ATTACK_FLYING_ONE;

    /** @var Attack Cru-Ailes */
    public Attack $ATTACK_FLYING_TWO;

    /** @var Attack Tornade */
    public Attack $ATTACK_FLYING_THREE;
 
    /** @var Attack Vol */
    public Attack $ATTACK_FLYING_FOUR;

    public function loadFlyingAttacks() {
        $this->ATTACK_FLYING_ONE = $this->attackRepository->find('ATTACK_FLYING_ONE');
        $this->ATTACK_FLYING_TWO = $this->attackRepository->find('ATTACK_FLYING_TWO');
        $this->ATTACK_FLYING_THREE = $this->attackRepository->find('ATTACK_FLYING_THREE');
        $this->ATTACK_FLYING_FOUR = $this->attackRepository->find('ATTACK_FLYING_FOUR');
    }

    /**
     * Danse Plume : Augmente la force et le pouvoir du lanceur pendant 3 tours.
     */
    public function ATTACK_FLYING_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FLYING_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_FLYING_ONE, $doCritical);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FLYING_ONE->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FLYING_ONE->getCriticalPower());

        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $target->addStatus($strengthBuff);
        $target->addStatus($powerBuff);
    }

    /**
     * Cru-Ailes : Inflige des dégâts physique à la cible, et augmente la force du lanceur pendant 3 tours.
     */
    public function ATTACK_FLYING_TWO(Fighter &$caster, Fighter &$target): void
    {
        $attack = __FUNCTION__;

        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->$attack);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->$attack, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->$attack->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->$attack->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->$attack->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->$attack->getCriticalPower());

        $strengthBuff = new StatisticModifierStatus(3, 'strength', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($strengthBuff);
    }

    /**
     * Tornade : Inflige des dégâts spéciaux à la cible, et augmente le pouvoir pendant 3 tours.
     */
    public function ATTACK_FLYING_THREE(Fighter &$caster, Fighter &$target): void
    {
        $attack = __FUNCTION__;

        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->$attack);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->$attack, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->$attack->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->$attack->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul des buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->$attack->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->$attack->getCriticalPower());

        $powerBuff = new StatisticModifierStatus(3, 'power', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

        $caster->addStatus($powerBuff);
    }

    /**
     * Vol : Attaque en deux tours. Lors du premier tour, augmente son Endurance et son Courage pendant 2 tours. Lors du second tour, inflige des dégâts mixtes à la cible.
     */
    public function ATTACK_FLYING_FOUR(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_FLYING_FOUR);
        
        /* - Premier passage - */
        if($caster->getNextMultipleStepAttack() === []){
            $this::$combatLog->addStringLog("{$caster->getName()} lance {$this->ATTACK_FLYING_FOUR->getName()} et s'envole dans les airs.");
            $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

            /* - Calcul des buff & Application - */
            $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_FLYING_FOUR->getStatusPower(), $caster->getPresence());
            $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_FLYING_FOUR->getCriticalPower());

            $staminaBuff = new StatisticModifierStatus(3, 'stamina', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
            $braveryBuff = new StatisticModifierStatus(3, 'bravery', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

            $caster->addStatus($staminaBuff);
            $caster->addStatus($braveryBuff);

            $caster->setNextMultipleStepAttack(['Attack' => $this->ATTACK_FLYING_FOUR, 'step' => 2]);
        }
        else if($caster->getNextMultipleStepAttack()['step'] === 2){
            /* - Second passage - */
            $this::$combatLog->addStringLog("{$caster->getName()} surgit du ciel et frappe {$target->getName()}");
            $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

            /* - Esquive - */
            $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
            if($doDodge){
                $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
        
                return ;
            }

            /* - Calcul des dégâts & Application - */
            $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_FLYING_FOUR->getPower(), $caster->getStrength());
            $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_FLYING_FOUR->getCriticalPower());

            $target->receiveDamage($totalRawPhysicalDamage, 0);

            $caster->resetNextMultipleStepAttack();
        }
    }
}