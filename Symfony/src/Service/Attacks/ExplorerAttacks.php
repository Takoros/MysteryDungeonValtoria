<?php

namespace App\Service\Attacks;

use App\Service\Combat\CharacterFighter;
use App\Service\Combat\Status\StatModifierStatus;
use App\Service\Combat\Status\StatusInterface;

class ExplorerAttacks extends AbstractAttacks
{
    public const ATTACK_LUTTE_NAME = 'Lutte';
    public const ATTACK_TRANCHE_NAME = 'Tranche';
    public const ATTACK_REFLET_NAME = 'Reflet';
    public const ATTACK_PANSEMENT_NAME = 'Pansement';

    /**
     * Attaque Offensive par défaut
     * PA : 0
     * Puissance : 2.5
     * Pui. Crit : 50%
     * Portée : Adversaires
     * 
     * Attaque Mixte par défaut.
     */

    public function Lutte(CharacterFighter &$lanceur, CharacterFighter &$cible, bool $hasCrit, bool $hasDodged, bool $hasStab){
        $physicalDamage = $this->physicalDamage(2.5, $lanceur->getTotalStrength());
        $specialDamage = $this->specialDamage(2.5, $lanceur->getTotalPower());

        $physicalDamageTaken = round($this->physicalDefense($physicalDamage, $cible->getLevel(), $cible->getTotalStamina()));
        $specialDamageTaken = round($this->specialDefense($specialDamage, $cible->getLevel(), $cible->getTotalBravery()));
        $damageTaken = $physicalDamageTaken + $specialDamageTaken;
        
        if($hasDodged){
            $damageTaken = null;
            $isCibleKo = false;
        }
        else {
            if($hasStab){
                $damageTaken = $damageTaken * self::STAB_DAMAGE;
            }

            if($hasCrit){
                $damageTaken = $damageTaken * 1.5;
            }

            $isCibleKo = $cible->takeDamage($damageTaken);
        }

        return $this->createAttackLog(self::ATTACK_LUTTE_NAME, $damageTaken, $hasCrit, $isCibleKo, $lanceur, $cible);
    }

    /**
     * Attaque Offensive
     * PA : 2
     * Puissance : 6
     * Pui. Crit : 90%
     * Portée : Adversaires
     * 
     * Inflige des dégâts physique à la cible.
     */
    public function Tranche(CharacterFighter &$lanceur, CharacterFighter &$cible, bool $hasCrit, bool $hasDodged, bool $hasStab){
        $physicalDamage = $this->physicalDamage(6, $lanceur->getTotalStrength());

        $physicalDamageTaken = round($this->physicalDefense($physicalDamage, $cible->getLevel(), $cible->getTotalStamina()));
        $damageTaken = $physicalDamageTaken;

        if($hasDodged){
            $damageTaken = null;
            $isCibleKo = false;
        }
        else {
            if($hasStab){
                $damageTaken = $damageTaken * self::STAB_DAMAGE;
            }
            
            if($hasCrit){
                $damageTaken = $damageTaken * 1.9;
            }

            $isCibleKo = $cible->takeDamage($damageTaken);
        }

        return $this->createAttackLog(self::ATTACK_TRANCHE_NAME, $damageTaken, $hasCrit, $isCibleKo, $lanceur, $cible);
    }

    /**
     * Attaque de Statut
     * PA : 1
     * Puissance : 0
     * Pui. Crit : 25%
     * Portée : Lanceur
     * 
     * Augmente l'agilité du lanceur (5).
     */
    public function Reflet(CharacterFighter &$lanceur, CharacterFighter &$cible, bool $hasCrit, bool $hasDodged, bool $hasStab){
        $givenAgility = 5 * ($lanceur->getTotalPresence() / 5) * 0.8;

        if($hasStab){
            $givenAgility = $givenAgility * self::STAB_DAMAGE;
        }
        
        if($hasCrit){
            $givenAgility = $givenAgility * 1.25;
        }

        $status = new StatModifierStatus($cible->getId(), 2, 'agility', $givenAgility, StatusInterface::TYPE_BUFF);

        $cible->addStatus($status);

        return $this->createAttackLog(self::ATTACK_REFLET_NAME, null, $hasCrit, null, $lanceur, $cible, $status);
    }

    /**
     * Attaque de Soutien
     * PA : 2
     * Puissance : 0
     * Pui. Crit : 50%
     * Portée : Allié avec le moins de PV
     * 
     * Soigne l'allié avec le moins de vitalité (7).
     */
    public function Pansement(CharacterFighter &$lanceur, CharacterFighter &$cible, bool $hasCrit, bool $hasDodged, bool $hasStab){
        $vitalityHealed = 7 * ($lanceur->getTotalPresence() / 5) * 0.8;
        
        if($hasStab){
            $vitalityHealed = $vitalityHealed * self::STAB_DAMAGE;
        }
        
        if($hasCrit){
            $vitalityHealed= $vitalityHealed * 1.25;
        }

        $cible->receiveHeal($vitalityHealed);
    }
}