<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Attack\FighterAttacks;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\StatusInterface;

trait GhostAttacks
{
    use AbstractAttacks;

    /** @var Attack Onde Folie */
    public Attack $ATTACK_GHOST_ONE;

    /** @var Attack Étonnement */
    public Attack $ATTACK_GHOST_TWO;

    /** @var Attack Ball'Ombre */
    public Attack $ATTACK_GHOST_THREE;

    /** @var Attack Châtiment */
    public Attack $ATTACK_GHOST_FOUR;

    public function loadGhostAttacks() {
        $this->ATTACK_GHOST_ONE = $this->attackRepository->find('ATTACK_GHOST_ONE');
        $this->ATTACK_GHOST_TWO = $this->attackRepository->find('ATTACK_GHOST_TWO');
        $this->ATTACK_GHOST_THREE = $this->attackRepository->find('ATTACK_GHOST_THREE');
        $this->ATTACK_GHOST_FOUR = $this->attackRepository->find('ATTACK_GHOST_FOUR');
    }

    /**
     * Onde Folie : Inflige Confusion à la cible.
     */
    public function ATTACK_GHOST_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictControlStatus(100, StatusInterface::TYPE_CONTROL_CONFUSION, $caster, $target);
        }
    }

    /**
     * Étonnement : Inflige des dégâts spéciaux à la cible, 30% de chance d'infliger Pétrification.
     */
    public function ATTACK_GHOST_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            $this->inflictControlStatus(30, StatusInterface::TYPE_CONTROL_PETRIFICATION, $caster, $target);
        }
    }

    /**
     * Ball'Ombre : Inflige des dégâts physique à la cible.
     */
    public function ATTACK_GHOST_THREE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
        }
    }

    /**
     * Châtiment : Inflige des dégâts spéciaux à la cible, augmente la puissance de l'attaque à 10, si la cible est affectée par un status de contrôle.
     */
    public function ATTACK_GHOST_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            if($target->getNumberOfCurrentControlStatus() > 0){
                $rawSpecialDamage = $this->calculateSpecialDamage(10, $caster->getPower());
                $totalRawSpecialDamage = $this->calculateValueAfterStabAndCritical($rawSpecialDamage, $attackDetails->isCritical, $attackDetails->isStab, $this->$ATTACK->getCriticalPower());
            
                $target->receiveDamage(0, $totalRawSpecialDamage);
            }
            else {
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);
            }
        }
    }
}