<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FireAttacks
{
    use AbstractAttacks;

    /** @var Attack Enhardir */
    public Attack $ATTACK_FIRE_ONE;

    /** @var Attack Flammèche */
    public Attack $ATTACK_FIRE_TWO;

    /** @var Attack Poing Feu */
    public Attack $ATTACK_FIRE_THREE;

    /** @var Attack Danse du Feu */
    public Attack $ATTACK_FIRE_FOUR;

    public function loadFireAttacks() {
        $this->ATTACK_FIRE_ONE = $this->attackRepository->find('ATTACK_FIRE_ONE');
        $this->ATTACK_FIRE_TWO = $this->attackRepository->find('ATTACK_FIRE_TWO');
        $this->ATTACK_FIRE_THREE = $this->attackRepository->find('ATTACK_FIRE_THREE');
        $this->ATTACK_FIRE_FOUR = $this->attackRepository->find('ATTACK_FIRE_FOUR');
    }

    /**
     * Enhardir : Augmente la force du lanceur.
     */
    public function ATTACK_FIRE_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['strength'], 3, $caster, $target);
    }

    /**
     * Flammèche : Inflige des dégâts spéciaux à la cible avec 20% de chance de brûler celle-ci.
     */
    public function ATTACK_FIRE_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictDamagingStatus(20, StatusInterface::TYPE_DAMAGING_BURN, $caster, $target);
        }
    }

    /**
     * Poing Feu : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_FIRE_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Danse du Feu : Inflige des dégâts mixtes à la cible.
     */
    public function ATTACK_FIRE_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_MIXED, $caster, $target);
        }
    }
}