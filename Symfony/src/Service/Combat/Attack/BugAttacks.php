<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait BugAttacks
{
    use AbstractAttacks;

    /** @var Attack Chargeur */
    public Attack $ATTACK_BUG_ONE;

    /** @var Attack Éclair */
    public Attack $ATTACK_BUG_TWO;

    /** @var Attack Crocs Éclair */
    public Attack $ATTACK_BUG_THREE;

    /** @var Attack Parabochaines */
    public Attack $ATTACK_BUG_FOUR;

    public function loadBugAttacks() {
        $this->ATTACK_BUG_ONE = $this->attackRepository->find('ATTACK_BUG_ONE');
        $this->ATTACK_BUG_TWO = $this->attackRepository->find('ATTACK_BUG_TWO');
        $this->ATTACK_BUG_THREE = $this->attackRepository->find('ATTACK_BUG_THREE');
        $this->ATTACK_BUG_FOUR = $this->attackRepository->find('ATTACK_BUG_FOUR');
    }

    /**
     * Sécrétion : Réduit la Vitesse de la cible.
     */
    public function ATTACK_BUG_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);
        
        if(!$attackDetails->hasDodged){
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['speed'], 2, $caster, $target);
        }
    }

    /**
     * Piqûre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_BUG_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Survinsecte : Inflige des dégâts spéciaux à tout les ennemis, diminie le pouvoir de ceux-ci.
     */
    public function ATTACK_BUG_THREE(Fighter &$caster, array &$targets): void
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
     * Papillodanse : Augmente le Pouvoir, le Courage et la Vitesse du lanceur pendant 3 tours.
     */
    public function ATTACK_BUG_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['power', 'bravery', 'speed'], 3, $caster, $target);
    }
}