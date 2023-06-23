<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait RockAttacks
{
    use AbstractAttacks;

    /** @var Attack Renforcement */
    public Attack $ATTACK_ROCK_ONE;

    /** @var Attack Jet Pierre */
    public Attack $ATTACK_ROCK_TWO;

    /** @var Attack Assourdir */
    public Attack $ATTACK_ROCK_THREE;

    /** @var Attack Lourdeur */
    public Attack $ATTACK_ROCK_FOUR;

    public function loadRockAttacks() {
        $this->ATTACK_ROCK_ONE = $this->attackRepository->find('ATTACK_ROCK_ONE');
        $this->ATTACK_ROCK_TWO = $this->attackRepository->find('ATTACK_ROCK_TWO');
        $this->ATTACK_ROCK_THREE = $this->attackRepository->find('ATTACK_ROCK_THREE');
        $this->ATTACK_ROCK_FOUR = $this->attackRepository->find('ATTACK_ROCK_FOUR');
    }

    /**
     * Renforcement : Augmente l'endurance du lanceur pendant 3 tours.
     */
    public function ATTACK_ROCK_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina'], 3, $caster, $target);
    }

    /**
     * Jet Pierre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_ROCK_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Assourdir : Inflige des dégâts spéciaux à la cible, avec 15% de chance de la rendre confuse.
     */
    public function ATTACK_ROCK_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictControlStatus(15, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Lourdeur : Inflige des dégâts physique et réduit la force ainsi que la coordination de la cible pendant 2 tours.
     */
    public function ATTACK_ROCK_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['strength', 'coordination'], 2, $caster, $target);
        }
    }
}