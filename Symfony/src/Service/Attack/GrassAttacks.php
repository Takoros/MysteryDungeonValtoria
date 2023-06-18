<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait GrassAttacks
{
    use AbstractAttacks;

    /** @var Attack Feuillage */
    public Attack $ATTACK_GRASS_ONE;

    /** @var Attack Fouet Lianes */
    public Attack $ATTACK_GRASS_TWO;

    /** @var Attack Vole-Vie */
    public Attack $ATTACK_GRASS_THREE;
 
    /** @var Attack Para-Spore */
    public Attack $ATTACK_GRASS_FOUR;

    public function loadGrassAttacks() {
        $this->ATTACK_GRASS_ONE = $this->attackRepository->find('ATTACK_GRASS_ONE');
        $this->ATTACK_GRASS_TWO = $this->attackRepository->find('ATTACK_GRASS_TWO');
        $this->ATTACK_GRASS_THREE = $this->attackRepository->find('ATTACK_GRASS_THREE');
        $this->ATTACK_GRASS_FOUR = $this->attackRepository->find('ATTACK_GRASS_FOUR');
    }

    /**
     * Feuillage : Inflige des dégâts spéciaux et réduit la coordination de la cible.
     */
    public function ATTACK_GRASS_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GRASS_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
 
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GRASS_ONE, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
     
            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_GRASS_ONE->getPower(), $caster->getPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doStab, $doCritical, $this->ATTACK_GRASS_ONE->getCriticalPower());

        $target->receiveDamage(0, $totalRawSpecialDamage);

        /* - Calcul du nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_GRASS_ONE->getStatusPower(), $caster->getPresence());
        $totalRawNerfAmount = $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doStab, $doCritical, $this->ATTACK_GRASS_ONE->getCriticalPower());

        $nerfStatus = new StatisticModifierStatus(2, 'coordination', $totalRawNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatus);
    }

    /**
     * Fouet Lianes : Inflige des dégâts physique à la cible, avec 15% de chance de la rendre confuse.
     */
    public function ATTACK_GRASS_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GRASS_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
         
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GRASS_TWO, $doCritical);
                
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
                
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
             
            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_GRASS_TWO->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doStab, $doCritical, $this->ATTACK_GRASS_TWO->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(15, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_CONFUSION, $this::$combatLog);
    
            $target->addStatus($controlStatus);
        }
    }

    /**
     * Vole-vie : Infliges des dégâts mixtes à la cible, récupérant 25% des dégâts infligés.
     */
    public function ATTACK_GRASS_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_GRASS_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
            
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GRASS_THREE, $doCritical);
                
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
                
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);
                
            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_GRASS_THREE->getPower(), $caster->getStrength());
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_GRASS_THREE->getPower(), $caster->getPower());
        
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doStab, $doCritical, $this->ATTACK_GRASS_THREE->getCriticalPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doStab, $doCritical, $this->ATTACK_GRASS_THREE->getCriticalPower());

        $damageReceived = $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);

        /* - Calcul des soins & Application - */
        $totalRawHealing = round($damageReceived / 4);

        $caster->receiveHealing($totalRawHealing);
    }

    /**
     * Para-Spore : Inflige Paralysie à la cible.
     */
    public function ATTACK_GRASS_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_GRASS_FOUR, false);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(100, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $controlStatus = new ControlStatus(ControlStatus::CONTROL_PARALYSIS, $this::$combatLog);
            
            $target->addStatus($controlStatus);
        }
    }
}