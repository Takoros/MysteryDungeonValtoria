<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FightingAttacks
{
    use AbstractAttacks;

    /** @var Attack Harmonie */
    public Attack $ATTACK_FIGHTING_ONE;

    /** @var Attack Poing Karaté */
    public Attack $ATTACK_FIGHTING_TWO;

    /** @var Attack Aurasphère */
    public Attack $ATTACK_FIGHTING_THREE;

    /** @var Attack Échauffement */
    public Attack $ATTACK_FIGHTING_FOUR;

    public function loadFightingAttacks() {
        $this->ATTACK_FIGHTING_ONE = $this->attackRepository->find('ATTACK_FIGHTING_ONE');
        $this->ATTACK_FIGHTING_TWO = $this->attackRepository->find('ATTACK_FIGHTING_TWO');
        $this->ATTACK_FIGHTING_THREE = $this->attackRepository->find('ATTACK_FIGHTING_THREE');
        $this->ATTACK_FIGHTING_FOUR = $this->attackRepository->find('ATTACK_FIGHTING_FOUR');
    }

    /**
     * Harmonie : Augmente la Présence et l'Impassibilité du lanceur pendant 3 tours.
     */
    public function ATTACK_FIGHTING_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['presence', 'impassiveness'], 3, $caster, $target);
    }

    /**
     * Poing Karaté :Inflige des dégâts physique à la cible.
     */
    public function ATTACK_FIGHTING_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Aurasphère : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_FIGHTING_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
        }
    }

    /**
     * Échauffement : Inflige des dégâts physique à la cible et augmente la force ainsi que le pouvoir du lanceur pendant 3 tours.
     */
    public function ATTACK_FIGHTING_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['strength', 'power'], 3, $caster, $caster);
        }
    }
}