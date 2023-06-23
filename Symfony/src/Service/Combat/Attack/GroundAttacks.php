<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait GroundAttacks
{
    use AbstractAttacks;

    /** @var Attack Jet de sable */
    public Attack $ATTACK_GROUND_ONE;

    /** @var Attack Tourbi-Sable */
    public Attack $ATTACK_GROUND_TWO;

    /** @var Attack Tir de boue */
    public Attack $ATTACK_GROUND_THREE;

    /** @var Attack Tunnel */
    public Attack $ATTACK_GROUND_FOUR;

    public function loadGroundAttacks() {
        $this->ATTACK_GROUND_ONE = $this->attackRepository->find('ATTACK_GROUND_ONE');
        $this->ATTACK_GROUND_TWO = $this->attackRepository->find('ATTACK_GROUND_TWO');
        $this->ATTACK_GROUND_THREE = $this->attackRepository->find('ATTACK_GROUND_THREE');
        $this->ATTACK_GROUND_FOUR = $this->attackRepository->find('ATTACK_GROUND_FOUR');
    }

    /**
     * Jet de sable : Réduit la Coordination de la cible pendant 2 tours.
     */
    public function ATTACK_GROUND_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['coordination'], 2, $caster, $target);
        }

    }

    /**
     * Tourbi-Sable : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_GROUND_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Tir de boue : Inflige des dégâts spéciaux à la cible et réduit sa Vitesse pendant 2 tours.
     */
    public function ATTACK_GROUND_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['speed'], 2, $caster, $target);
        }
    }

    /**
     * Tunnel : Attaque en deux tours. Lors du premier tour, augmente son Endurance et son Courage pendant 2 tours. Lors du second tour, inflige des dégâts physique à la cible.
     */
    public function ATTACK_GROUND_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        if($caster->getNextMultipleStepAttack() === []){
            $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina', 'bravery'], 2, $caster, $caster);

            $caster->setNextMultipleStepAttack(['Attack' => $this->ATTACK_GROUND_FOUR, 'step' => 2]);
        }
        else if($caster->getNextMultipleStepAttack()['step'] === 2){
            $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);

            $caster->resetNextMultipleStepAttack();
        }
    }
}