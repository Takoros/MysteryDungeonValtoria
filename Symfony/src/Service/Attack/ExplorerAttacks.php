<?php

namespace App\Service\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait ExplorerAttacks
{
    use AbstractAttacks;

    /** @var Attack Lutte */
    public Attack $ATTACK_EXPLORER_BASE;
    
    /** @var Attack Tranche */
    public Attack $ATTACK_EXPLORER_ONE;

    /** @var Attack Reflet */
    public Attack $ATTACK_EXPLORER_TWO;

    /** @var Attack Regard Touchant */
    public Attack $ATTACK_EXPLORER_THREE;
 
    /** @var Attack Encouragement */
    public Attack $ATTACK_EXPLORER_FOUR;

    /** @var Attack Léchouille */
    public Attack $ATTACK_EXPLORER_FIVE;

    /** @var Attack Équilibre */
    public Attack $ATTACK_EXPLORER_SIX;

    /** @var Attack Coup Double */
    public Attack $ATTACK_EXPLORER_SEVEN;

    /** @var Attack Pansement */
    public Attack $ATTACK_EXPLORER_EIGHT;

    /**
     * Equivalent of __construct()
     */
    public function loadExplorerAttacks() {
        $this->ATTACK_EXPLORER_BASE = $this->attackRepository->find('ATTACK_EXPLORER_BASE');
        $this->ATTACK_EXPLORER_ONE = $this->attackRepository->find('ATTACK_EXPLORER_ONE');
        $this->ATTACK_EXPLORER_TWO = $this->attackRepository->find('ATTACK_EXPLORER_TWO');
        $this->ATTACK_EXPLORER_THREE = $this->attackRepository->find('ATTACK_EXPLORER_THREE');
        $this->ATTACK_EXPLORER_FOUR = $this->attackRepository->find('ATTACK_EXPLORER_FOUR');
        $this->ATTACK_EXPLORER_FIVE = $this->attackRepository->find('ATTACK_EXPLORER_FIVE');
        $this->ATTACK_EXPLORER_SIX = $this->attackRepository->find('ATTACK_EXPLORER_SIX');
        $this->ATTACK_EXPLORER_SEVEN = $this->attackRepository->find('ATTACK_EXPLORER_SEVEN');
        $this->ATTACK_EXPLORER_EIGHT = $this->attackRepository->find('ATTACK_EXPLORER_EIGHT');
    }

    /**
     * Lutte : Inflige des dégâts mixte à la cible.
     */
    public function ATTACK_EXPLORER_BASE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_BASE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_BASE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_EXPLORER_BASE->getPower(), $caster->getStrength());
        $rawSpecialDamage = $this->calculateSpecialDamage($this->ATTACK_EXPLORER_BASE->getPower(), $caster->getPower());
        
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_EXPLORER_BASE->getCriticalPower());
        $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $doCritical, $doStab, $this->ATTACK_EXPLORER_BASE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, $totalRawSpecialDamage);
    }

    /**
     * Tranche : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_EXPLORER_ONE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_ONE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_ONE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_EXPLORER_ONE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_EXPLORER_ONE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Reflet : Augmente l'agilité du lanceur pendant 3 tours
     */
    public function ATTACK_EXPLORER_TWO(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_TWO);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_TWO, $doCritical);

        /* - Calcul du buff & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_EXPLORER_TWO->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_EXPLORER_TWO->getCriticalPower());

        $buffStatus = new StatisticModifierStatus(3, 'agility', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);
        $target->addStatus($buffStatus);
    }

    /**
     * Regard Touchant : Réduit la force et le pouvoir de la cible pendant 2 tours.
     */
    public function ATTACK_EXPLORER_THREE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_THREE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), $target->getAgility());
        
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_THREE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des nerf & Application - */
        $rawNerfAmount = $this->calculateRawNerfStatusValue($this->ATTACK_EXPLORER_THREE->getStatusPower(), $caster->getPresence());
        $totalNerfAmount =  $this->calculateValueAfterStabAndCritical($rawNerfAmount, $doCritical, $doStab, $this->ATTACK_EXPLORER_THREE->getCriticalPower());

        $nerfStatusStrength = new StatisticModifierStatus(2, 'strength', $totalNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);
        $nerfStatusPower = new StatisticModifierStatus(2, 'power', $totalNerfAmount, StatusInterface::TYPE_NERF, $this::$combatLog);

        $target->addStatus($nerfStatusStrength);
        $target->addStatus($nerfStatusPower);
    }

    /**
     * Encouragement : Augmente la vitesse de l'équipe alliée (y compris soi-même) pendant 3 tours.
     */
    public function ATTACK_EXPLORER_FOUR(Fighter &$caster, array &$targets): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_FOUR);
        $doCritical = $this->doCritical($caster->getCoordination(), $this->averageFighterLevel($targets), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_FOUR, $doCritical);
        
        /* - Calcul des buffs & Application - */
        $rawBuffAmount = $this->calculateBuffStatusValue($this->ATTACK_EXPLORER_FOUR->getStatusPower(), $caster->getPresence());
        $totalBuffAmount = $this->calculateValueAfterStabAndCritical($rawBuffAmount, $doCritical, $doStab, $this->ATTACK_EXPLORER_FOUR->getCriticalPower());
        
        foreach ($targets as $target) {
            $buffStatus = new StatisticModifierStatus(3, 'speed', $totalBuffAmount, StatusInterface::TYPE_BUFF, $this::$combatLog);

            $target->addStatus($buffStatus);
        }
    }

    /**
     * Léchouille : Inflige des dégâts physique à la cible, avec 30% de chance de paralyser la cible.
     */
    public function ATTACK_EXPLORER_FIVE(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_FIVE);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);

        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_FIVE, $doCritical);

        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_EXPLORER_FIVE->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_EXPLORER_FIVE->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);

        /* - Calcul du control & Application - */
        if($this->doInflictControlStatus(30, $caster->getPresence(), $target->getImpassiveness(), $target->getNumberOfCurrentControlStatus())){
            $paralysis = new ControlStatus(ControlStatus::CONTROL_PARALYSIS, $this::$combatLog);

            $target->addStatus($paralysis);
        }
    }

    /**
     * Équilibre : Soigne de 1 à 3 status négatifs (1 : 45%, 2 : 35%, 3 : 20%).
     */
    public function ATTACK_EXPLORER_SIX(Fighter &$caster, Fighter &$target): void
    {
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_SIX, false);
        
        /* - Calcul du nombre d'effet négatif subis - */
        $targetNegativeStatus = $target->getAllNegativeStatus();

        if(count($targetNegativeStatus) < 1){
            $this::$combatLog->addStringLog("Cela n'a aucun effet.");
            return ;
        }

        /* - Calcul du nombre d'effets négatifs retirés - */
        $diceResult = rand(1, 100);

        if($diceResult >= 80){
            $numberOfNegativeStatusPurged = 3;
        }
        else if($diceResult >= 45){
            $numberOfNegativeStatusPurged = 2;
        }
        else {
            $numberOfNegativeStatusPurged = 1;
        }

        if($numberOfNegativeStatusPurged > count($targetNegativeStatus)){
            $numberOfNegativeStatusPurged = count($targetNegativeStatus);
        }

        /* - Purge des effets négatifs - */
        for ($i=0; $i < $numberOfNegativeStatusPurged; $i++) { 
            $targetNegativeStatus[$i]->getPurged($target);
        }

        $target->removePurgedStatus();
    }

    /**
     * Coup Double : Inflige des dégâts physique à la cible, deux fois
     */
    public function ATTACK_EXPLORER_SEVEN(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_SEVEN);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);
        
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_SEVEN, $doCritical);
        
        /* - Esquive - */
        $doDodge = $this->doDodge($caster->getCoordination(), $caster->getLevel(), $target->getAgility());
        
        if($doDodge){
            $this::$combatLog->addReceiveDamageLog(CombatLog::HAS_DODGED, $target);

            return ;
        }

        /* - Calcul des dégâts & Application - */
        $rawPhysicalDamage = $this->calculatePhysicalDamage($this->ATTACK_EXPLORER_SEVEN->getPower(), $caster->getStrength());
        $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $doCritical, $doStab, $this->ATTACK_EXPLORER_SEVEN->getCriticalPower());

        $target->receiveDamage($totalRawPhysicalDamage, 0);
        $target->receiveDamage($totalRawPhysicalDamage, 0);
    }

    /**
     * Pansement : Soigne l'allié avec le moins de vitalité
     */
    public function ATTACK_EXPLORER_EIGHT(Fighter &$caster, Fighter &$target): void
    {
        /* - Stabs & Critique - */
        $doStab = $this->doStab($caster, $this->ATTACK_EXPLORER_EIGHT);
        $doCritical = $this->doCritical($caster->getCoordination(), $target->getLevel(), null);
        $this::$combatLog->addUseAttackLog($caster, $this->ATTACK_EXPLORER_EIGHT, $doCritical);

        /* - Calcul du soin & Application - */
        $rawHealing = $this->calculateHealing($this->ATTACK_EXPLORER_EIGHT->getPower(), $caster->getPresence());
        $totalRawHealing = $this->calculateValueAfterStabAndCritical($rawHealing, $doCritical, $doStab, $this->ATTACK_EXPLORER_EIGHT->getCriticalPower());

        $target->receiveHealing($totalRawHealing);
    }
}