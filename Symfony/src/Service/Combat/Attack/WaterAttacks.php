<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait WaterAttacks
{
    use AbstractAttacks;

    /** @var Attack Absorb'O */
    public Attack $ATTACK_WATER_ONE;

    /** @var Attack Pistolet à O */
    public Attack $ATTACK_WATER_TWO;

    /** @var Attack Aqua-Jet */
    public Attack $ATTACK_WATER_THREE;

    /** @var Attack Parabochaines */
    public Attack $ATTACK_WATER_FOUR;

    public function loadWaterAttacks() {
        $this->ATTACK_WATER_ONE = $this->attackRepository->find('ATTACK_WATER_ONE');
        $this->ATTACK_WATER_TWO = $this->attackRepository->find('ATTACK_WATER_TWO');
        $this->ATTACK_WATER_THREE = $this->attackRepository->find('ATTACK_WATER_THREE');
        $this->ATTACK_WATER_FOUR = $this->attackRepository->find('ATTACK_WATER_FOUR');
    }

    /**
     * Absorb'O : Soigne le lanceur.
     */
    public function ATTACK_WATER_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

        if(!$attackDetails->hasDodged){
            $this->inflictHealing($this->$ATTACK, $attackDetails, $caster, $caster);    
        }
    }

    /**
     * Pistolet à O : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_WATER_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
        }
    }

    /**
     * Aqua-Jet : Inflige des dégâts physique à la cible, avec 30% de chance de la rendre confuse.
     */
    public function ATTACK_WATER_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictControlStatus(30, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Repli : Augmente l'Endurance du lanceur pendant 3 tours.
     */
    public function ATTACK_WATER_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina'], 3, $caster, $target);
    }
}