<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait FairyAttacks
{
    use AbstractAttacks;

    /** @var Attack Doux Baiser */
    public Attack $ATTACK_FAIRY_ONE;

    /** @var Attack Voix Enjôleuse */
    public Attack $ATTACK_FAIRY_TWO;

    /** @var Attack Vigilance */
    public Attack $ATTACK_FAIRY_THREE;

    /** @var Attack Câlinerie */
    public Attack $ATTACK_FAIRY_FOUR;

    public function loadFairyAttacks() {
        $this->ATTACK_FAIRY_ONE = $this->attackRepository->find('ATTACK_FAIRY_ONE');
        $this->ATTACK_FAIRY_TWO = $this->attackRepository->find('ATTACK_FAIRY_TWO');
        $this->ATTACK_FAIRY_THREE = $this->attackRepository->find('ATTACK_FAIRY_THREE');
        $this->ATTACK_FAIRY_FOUR = $this->attackRepository->find('ATTACK_FAIRY_FOUR');
    }

    /**
     * Doux Baiser : Inflige Confusion à la cible.
     */
    public function ATTACK_FAIRY_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictControlStatus(100, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Voix Enjôleuse : Inflige des dégâts spéciaux à la cible.
     */
    public function ATTACK_FAIRY_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
        }
    }

    /**
     * Vigilance : Augmente l'impassibilité du lanceur pendant 3 tours.
     */
    public function ATTACK_FAIRY_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['impassiveness'], 3, $caster, $target);
    }

    /**
     * Câlinerie : Inflige des dégâts physique à la cible et réduit sa force pendant 2 tours.
     */
    public function ATTACK_FAIRY_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['strength'], 2, $caster, $target);
        }
    }
}