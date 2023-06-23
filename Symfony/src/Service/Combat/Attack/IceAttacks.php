<?php

namespace App\Service\Combat\Attack;

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
        $ATTACK = __FUNCTION__;

        $this::$combatLog->addAttackLog($caster, $this->$ATTACK, false);

        /* - Calcul du nombre d'effet positifs subis - */
        $targetPositiveStatus = $target->getAllPositiveStatus();

        if(count($targetPositiveStatus) < 1){
            $this::$combatLog->addAttackNoEffectLog();
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

        $target->removeObsoleteStatus();
    }

    /**
     * Poudreuse : Inflige des dégâts spéciaux à la cible, et réduit son endurance pendant 2 tours.
     */
    public function ATTACK_ICE_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['stamina'], 2, $caster, $target);
        }
    }

    /**
     * Éclats Glace : Inflige des dégâts physique à la cible, et augmente la vitesse du lanceur pendant 3 tours.
     */
    public function ATTACK_ICE_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['speed'], 3, $caster, $caster);
        }
    }

    /**
     * Onde Boréale : Inflige des dégâts spéciaux à la cible, et réduit sa vitesse pendant 2 tours.
     */
    public function ATTACK_ICE_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['speed'], 2, $caster, $target);
        }
    }
}