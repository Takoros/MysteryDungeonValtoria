<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait NormalAttacks
{
    use AbstractAttacks;

    /** @var Attack Flair */
    public Attack $ATTACK_NORMAL_ONE;

    /** @var Attack Charge */
    public Attack $ATTACK_NORMAL_TWO;

    /** @var Attack Météores */
    public Attack $ATTACK_NORMAL_THREE;

    /** @var Attack Abri */
    public Attack $ATTACK_NORMAL_FOUR;

    public function loadNormalAttacks() {
        $this->ATTACK_NORMAL_ONE = $this->attackRepository->find('ATTACK_NORMAL_ONE');
        $this->ATTACK_NORMAL_TWO = $this->attackRepository->find('ATTACK_NORMAL_TWO');
        $this->ATTACK_NORMAL_THREE = $this->attackRepository->find('ATTACK_NORMAL_THREE');
        $this->ATTACK_NORMAL_FOUR = $this->attackRepository->find('ATTACK_NORMAL_FOUR');
    }

    /**
     * Flair : Purge les bonus d'agilité de la cible, et augmente la Coordination du lanceur pendant 3 tours.
     */
    public function ATTACK_NORMAL_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

        $target->purgeAllModifierOfStatistic('agility', StatusInterface::TYPE_BUFF);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['coordination'], 2, $caster, $caster);
    }

    /**
     * Charge : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_NORMAL_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Météores : Inflige des dégâts spéciaux à la cible, et augmente le pouvoir du lanceur pendant 3 tours.
     */
    public function ATTACK_NORMAL_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['power'], 3, $caster, $caster);
        }
    }

    /**
     * Abri : Augmente l'Endurance et le Courage du lanceur pendant 2 tours.
     */
    public function ATTACK_NORMAL_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina', 'bravery'], 2, $caster, $target);
    }
}