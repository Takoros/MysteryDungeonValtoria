<?php

namespace App\Service\Combat\Attack;

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
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_MIXED, $caster, $target);
        }
    }

    /**
     * Tranche : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_EXPLORER_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Reflet : Augmente l'agilité du lanceur pendant 3 tours
     */
    public function ATTACK_EXPLORER_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['agility'], 3, $caster, $target);
    }

    /**
     * Regard Touchant : Réduit la force et le pouvoir de la cible pendant 2 tours.
     */
    public function ATTACK_EXPLORER_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);
        
        if(!$attackDetails->hasDodged){
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['strength', 'power'], 2, $caster, $target);
        }
    }

    /**
     * Encouragement : Augmente la vitesse de l'équipe alliée (y compris soi-même) pendant 3 tours.
     */
    public function ATTACK_EXPLORER_FOUR(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['speed'], 3, $caster, $target);
        }
    }

    /**
     * Léchouille : Inflige des dégâts physique à la cible, avec 30% de chance de paralyser la cible.
     */
    public function ATTACK_EXPLORER_FIVE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictControlStatus(30, StatusInterface::TYPE_CONTROL_PARALYSIS, $caster, $target);
        }
    }

    /**
     * Équilibre : Soigne de 1 à 3 status négatifs (1 : 45%, 2 : 35%, 3 : 20%).
     */
    public function ATTACK_EXPLORER_SIX(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $this::$combatLog->addAttackLog($caster, $this->$ATTACK, false);

        /* - Calcul du nombre d'effet négatif subis - */
        $targetNegativeStatus = $target->getAllNegativeStatus();

        if(count($targetNegativeStatus) < 1){
            $this::$combatLog->addAttackNoEffectLog();
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

        $target->removeObsoleteStatus();
    }

    /**
     * Coup Double : Inflige des dégâts physique à la cible, deux fois
     */
    public function ATTACK_EXPLORER_SEVEN(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }

        $hasDodgedSecondAttack = $this->hasDodged($caster, $target);

        if(!$hasDodgedSecondAttack){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Pansement : Soigne l'allié avec le moins de vitalité
     */
    public function ATTACK_EXPLORER_EIGHT(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        
        $this->inflictHealing($this->$ATTACK, $attackDetails, $caster, $target);
    }
}