<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait SteelAttacks
{
    use AbstractAttacks;

    /** @var Attack Analyse */
    public Attack $ATTACK_STEEL_ONE;

    /** @var Attack Lancécrou */
    public Attack $ATTACK_STEEL_TWO;

    /** @var Attack Griffes Acier */
    public Attack $ATTACK_STEEL_THREE;

    /** @var Attack Bombe Aimant */
    public Attack $ATTACK_STEEL_FOUR;

    public function loadSteelAttacks() {
        $this->ATTACK_STEEL_ONE = $this->attackRepository->find('ATTACK_STEEL_ONE');
        $this->ATTACK_STEEL_TWO = $this->attackRepository->find('ATTACK_STEEL_TWO');
        $this->ATTACK_STEEL_THREE = $this->attackRepository->find('ATTACK_STEEL_THREE');
        $this->ATTACK_STEEL_FOUR = $this->attackRepository->find('ATTACK_STEEL_FOUR');
    }

    /**
     * Analyse : Augmente la Coordination de l'équipe alliée (y compris soi-même) pendant 3 tours.
     */
    public function ATTACK_STEEL_ONE(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['coordination'], 3, $caster, $target);
        }
    }

    /**
     * Lancécrou : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_STEEL_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
        }
    }

    /**
     * Griffes Acier : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_STEEL_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Bombe Aimant : Inflige des dégâts physique à tout les adversaires.
     */
    public function ATTACK_STEEL_FOUR(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            }
        }
    }
}