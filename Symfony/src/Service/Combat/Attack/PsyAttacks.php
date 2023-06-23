<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait PsyAttacks
{
    use AbstractAttacks;

    /** @var Attack Hypnose */
    public Attack $ATTACK_PSY_ONE;

    /** @var Attack Choc Mental */
    public Attack $ATTACK_PSY_TWO;

    /** @var Attack Psykoud'Boul */
    public Attack $ATTACK_PSY_THREE;

    /** @var Attack Vibra Soin */
    public Attack $ATTACK_PSY_FOUR;

    public function loadPsyAttacks() {
        $this->ATTACK_PSY_ONE = $this->attackRepository->find('ATTACK_PSY_ONE');
        $this->ATTACK_PSY_TWO = $this->attackRepository->find('ATTACK_PSY_TWO');
        $this->ATTACK_PSY_THREE = $this->attackRepository->find('ATTACK_PSY_THREE');
        $this->ATTACK_PSY_FOUR = $this->attackRepository->find('ATTACK_PSY_FOUR');
    }

    /**
     * Hypnose : Inflige sommeil à la cible
     */
    public function ATTACK_PSY_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictControlStatus(100, StatusInterface::TYPE_CONTROL_SLEEP, $caster, $target);
        }
    }

    /**
     * Choc Mental : Inflige des dégâts spéciaux à la cible et possède 10% de chance d'infliger confusion à celle-ci.
     */
    public function ATTACK_PSY_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictControlStatus(10, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Psykoud'Boul : Inflige des dégâts physique à la cible
     */
    public function ATTACK_PSY_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Vibra Soin : Soigne tout les alliés du groupe
     */
    public function ATTACK_PSY_FOUR(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $this->inflictHealing($this->$ATTACK, $attackDetails, $caster, $target);
        }
    }
}