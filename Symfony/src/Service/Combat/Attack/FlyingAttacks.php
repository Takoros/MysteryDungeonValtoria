<?php

namespace App\Service\Combat\Attack;

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
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['power', 'strength'], 3, $caster, $target);
    }

    /**
     * Cru-Ailes : Inflige des dégâts physique à la cible, et augmente la force du lanceur pendant 3 tours.
     */
    public function ATTACK_FLYING_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['strength'], 3, $caster, $caster);
        }
    }

    /**
     * Tornade : Inflige des dégâts spéciaux à la cible, et augmente le pouvoir pendant 3 tours.
     */
    public function ATTACK_FLYING_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['power'], 3, $caster, $caster);
        }
    }

    /**
     * Vol : Attaque en deux tours. Lors du premier tour, augmente son Endurance et son Courage pendant 2 tours. Lors du second tour, inflige des dégâts mixtes à la cible.
     */
    public function ATTACK_FLYING_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        if($caster->getNextMultipleStepAttack() === []){
            $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina', 'bravery'], 2, $caster, $caster);

            $caster->setNextMultipleStepAttack(['Attack' => $this->ATTACK_FLYING_FOUR, 'step' => 2]);
        }
        else if($caster->getNextMultipleStepAttack()['step'] === 2){
            $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_MIXED, $caster, $target);

            $caster->resetNextMultipleStepAttack();
        }
    }
}