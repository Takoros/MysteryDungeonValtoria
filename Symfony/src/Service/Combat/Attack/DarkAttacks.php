<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait DarkAttacks
{
    use AbstractAttacks;

    /** @var Attack Fumée Protectrice */
    public Attack $ATTACK_DARK_ONE;

    /** @var Attack Morsure */
    public Attack $ATTACK_DARK_TWO;

    /** @var Attack Aboiement */
    public Attack $ATTACK_DARK_THREE;

    /** @var Attack Feinte */
    public Attack $ATTACK_DARK_FOUR;

    public function loadDarkAttacks() {
        $this->ATTACK_DARK_ONE = $this->attackRepository->find('ATTACK_DARK_ONE');
        $this->ATTACK_DARK_TWO = $this->attackRepository->find('ATTACK_DARK_TWO');
        $this->ATTACK_DARK_THREE = $this->attackRepository->find('ATTACK_DARK_THREE');
        $this->ATTACK_DARK_FOUR = $this->attackRepository->find('ATTACK_DARK_FOUR');
    }

    /**
     * Fumée Protectrice : Augmente l'Endurance, le Courage et la Vitalité du lanceur pendant 3 tours.
     */
    public function ATTACK_DARK_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina', 'bravery', 'vitality'], 3, $caster, $target);

    }

    /**
     * Morsure : Inflige des dégâts physique à la cible, 30% de chance d'infliger Pétrification.
     */
    public function ATTACK_DARK_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictControlStatus(30, StatusInterface::TYPE_CONTROL_PETRIFICATION, $caster, $target);
        }
    }

    /**
     * Aboiement : Inflige des dégâts spéciaux à tout les ennemis, réduisant leur Pouvoir pendant 2 tours.
     */
    public function ATTACK_DARK_THREE(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
                $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['power'], 2, $caster, $target);
            }
        }
    }

    /**
     * Feinte : Inflige des dégâts mixtes à la cible. Ne peut pas être esquivé.
     */
    public function ATTACK_DARK_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_MIXED, $caster, $target);
    }
}