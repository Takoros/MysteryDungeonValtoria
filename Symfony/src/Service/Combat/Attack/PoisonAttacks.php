<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;

trait PoisonAttacks
{
    use AbstractAttacks;

    /** @var Attack Toxik */
    public Attack $ATTACK_POISON_ONE;

    /** @var Attack Dard-Venin */
    public Attack $ATTACK_POISON_TWO;

    /** @var Attack Acide */
    public Attack $ATTACK_POISON_THREE;

    /** @var Attack Choc Venin */
    public Attack $ATTACK_POISON_FOUR;

    public function loadPoisonAttacks() {
        $this->ATTACK_POISON_ONE = $this->attackRepository->find('ATTACK_POISON_ONE');
        $this->ATTACK_POISON_TWO = $this->attackRepository->find('ATTACK_POISON_TWO');
        $this->ATTACK_POISON_THREE = $this->attackRepository->find('ATTACK_POISON_THREE');
        $this->ATTACK_POISON_FOUR = $this->attackRepository->find('ATTACK_POISON_FOUR');
    }

    /**
     * Toxik : Inflige Poison Grave à la cible.
     */
    public function ATTACK_POISON_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->inflictDamagingStatus(100, StatusInterface::TYPE_DAMAGING_BAD_POISON, $caster, $target);
        }
    }

    /**
     * Dard-Venin : Inflige des dégâts physique à la cible, a 30% de chance d'infliger Poison.
     */
    public function ATTACK_POISON_TWO(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_PHYSICAL, $caster, $target);
            $this->inflictDamagingStatus(30, StatusInterface::TYPE_DAMAGING_POISON, $caster, $target);
        }
    }

    /**
     * Acide : Inflige des dégâts spéciaux à tout les ennemis, de plus, possède 10% de chance de réduire la force de celle-ci pendant 2 tours.
     */
    public function ATTACK_POISON_THREE(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);

                if($this->doInflictDamagingStatus(10, $caster->getPresence(), $target->getImpassiveness())){
                    $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['strength'], 2, $caster, $target);
                }

            }
        }
    }

    /**
     * Choc Venin : Inflige des dégâts spéciaux à la cible, si celle-ci est empoisonnée, infligé également des dégâts physique de puissance 2 en utilisant la Présence comme statistique.
     */
    public function ATTACK_POISON_FOUR(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, true);

        if(!$attackDetails->hasDodged){
            $this->dealDamage($this->$ATTACK, $attackDetails, FighterAttacks::DAMAGE_TYPE_SPECIAL, $caster, $target);

            if($target->isAffectedByStatus(StatusInterface::TYPE_DAMAGING_POISON) || $target->isAffectedByStatus(StatusInterface::TYPE_DAMAGING_BAD_POISON)){
                $rawPhysicalDamage = $this->calculatePhysicalDamage(2, $caster->getPresence());
                $totalRawPhysicalDamage = $this->calculateValueAfterStabAndCritical($rawPhysicalDamage, $attackDetails->isCritical, $attackDetails->isStab, $this->$ATTACK->getCriticalPower());
            
                $target->receiveDamage($totalRawPhysicalDamage, 0);
            }
        }
    }
}