<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait ElectricAttacks
{
    use AbstractAttacks;

    /** @var Attack Chargeur */
    public Attack $ATTACK_ELECTRIC_ONE;

    /** @var Attack Éclair */
    public Attack $ATTACK_ELECTRIC_TWO;

    /** @var Attack Crocs Éclair */
    public Attack $ATTACK_ELECTRIC_THREE;

    /** @var Attack Parabochaines */
    public Attack $ATTACK_ELECTRIC_FOUR;

    public function loadElectricAttacks() {
        $this->ATTACK_ELECTRIC_ONE = $this->attackRepository->find('ATTACK_ELECTRIC_ONE');
        $this->ATTACK_ELECTRIC_TWO = $this->attackRepository->find('ATTACK_ELECTRIC_TWO');
        $this->ATTACK_ELECTRIC_THREE = $this->attackRepository->find('ATTACK_ELECTRIC_THREE');
        $this->ATTACK_ELECTRIC_FOUR = $this->attackRepository->find('ATTACK_ELECTRIC_FOUR');
    }

    /**
     * Chargeur : Augmente la vitesse et l'agilité du lanceur pendant 3 tours.
     */
    public function ATTACK_ELECTRIC_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['agility', 'speed'], 3, $caster, $target);

    }

    /**
     * Éclair : Inflige des dégâts spéciaux à la cible, avec 30% de chance de paralyser la cible.
     */
    public function ATTACK_ELECTRIC_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictControlStatus(30, StatusInterface::TYPE_CONTROL_PARALYSIS, $caster, $target);
        }
    }

    /**
     * Crocs Éclair : Inflige des dégâts physique à la cible et réduit son endurance.
     */
    public function ATTACK_ELECTRIC_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['stamina'], 2, $caster, $target);
        }
    }

    /**
     * Parabochaines : Inflige des dégâts spéciaux, réduit la vitesse, l'agilité et la coordination de la cible pendant 3 tours.
     */
    public function ATTACK_ELECTRIC_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['speed', 'agility', 'coordination'], 3, $caster, $target);
        }
    }
}