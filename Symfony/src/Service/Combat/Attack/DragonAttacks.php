<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait DragonAttacks
{
    use AbstractAttacks;

    /** @var Attack Danse Draco */
    public Attack $ATTACK_DRAGON_ONE;

    /** @var Attack Ouragan */
    public Attack $ATTACK_DRAGON_TWO;

    /** @var Attack Abattage */
    public Attack $ATTACK_DRAGON_THREE;

    /** @var Attack Dracacophonie */
    public Attack $ATTACK_DRAGON_FOUR;

    public function loadDragonAttacks() {
        $this->ATTACK_DRAGON_ONE = $this->attackRepository->find('ATTACK_DRAGON_ONE');
        $this->ATTACK_DRAGON_TWO = $this->attackRepository->find('ATTACK_DRAGON_TWO');
        $this->ATTACK_DRAGON_THREE = $this->attackRepository->find('ATTACK_DRAGON_THREE');
        $this->ATTACK_DRAGON_FOUR = $this->attackRepository->find('ATTACK_DRAGON_FOUR');
    }

    /**
     * Danse Draco : Augmente la vitesse et la force du lanceur
     */
    public function ATTACK_DRAGON_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['speed', 'strength'], 3, $caster, $target);
    }

    /**
     * Ouragan : Inflige des dégâts spéciaux à tout les ennemis.
     */
    public function ATTACK_DRAGON_TWO(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            }
        }
    }

    /**
     * Abattage : Inflige des dégâts physiques à tout les ennemis et réduit leur pouvoir pendant 2 tours.
     */
    public function ATTACK_DRAGON_THREE(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
                $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['power'], 2, $caster, $target);
            }
        }
    }

    /**
     * Dracacophonie : S'auto inflige des dégâts mixtes, mais augmente sa force, son pouvoir, sa vitesse et sa coordination pendant 3 tours.
     */
    public function ATTACK_DRAGON_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);

        $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_MIXED, $caster, $target);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['speed', 'strength', 'power', 'coordination'], 3, $caster, $target);
    }
}