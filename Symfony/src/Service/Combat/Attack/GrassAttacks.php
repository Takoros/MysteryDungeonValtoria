<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait GrassAttacks
{
    use AbstractAttacks;

    /** @var Attack Feuillage */
    public Attack $ATTACK_GRASS_ONE;

    /** @var Attack Fouet Lianes */
    public Attack $ATTACK_GRASS_TWO;

    /** @var Attack Vole-Vie */
    public Attack $ATTACK_GRASS_THREE;

    /** @var Attack Para-Spore */
    public Attack $ATTACK_GRASS_FOUR;

    public function loadGrassAttacks() {
        $this->ATTACK_GRASS_ONE = $this->attackRepository->find('ATTACK_GRASS_ONE');
        $this->ATTACK_GRASS_TWO = $this->attackRepository->find('ATTACK_GRASS_TWO');
        $this->ATTACK_GRASS_THREE = $this->attackRepository->find('ATTACK_GRASS_THREE');
        $this->ATTACK_GRASS_FOUR = $this->attackRepository->find('ATTACK_GRASS_FOUR');
    }

    /**
     * Feuillage : Inflige des dégâts spéciaux et réduit la coordination de la cible.
     */
    public function ATTACK_GRASS_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['coordination'], 2, $caster, $target);
        }
    }

    /**
     * Fouet Lianes : Inflige des dégâts physique à la cible, avec 15% de chance de la rendre confuse.
     */
    public function ATTACK_GRASS_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictControlStatus(15, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Vole-vie : Infliges des dégâts mixtes à la cible, récupérant 25% des dégâts infligés.
     */
    public function ATTACK_GRASS_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $damageDealt = $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);

            $caster->receiveHealing($damageDealt / 4);
        }
    }

    /**
     * Para-Spore : Inflige Paralysie à la cible.
     */
    public function ATTACK_GRASS_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictControlStatus(100, StatusInterface::TYPE_CONTROL_PARALYSIS, $caster, $target);
        }
    }
}