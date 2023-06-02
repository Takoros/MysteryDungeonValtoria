<?php

namespace App\Service\Attacks;

use App\Repository\AttackRepository;
use App\Service\Combat\CharacterFighter;
use App\Service\Combat\Status\StatusInterface;

class AbstractAttacks
{
    const STAB_DAMAGE = 1.25;

    const ATTACK_SCOPE_SELF = "self";

    const ATTACK_SCOPE_ENEMY_TEAM = "enemy_team";
    const ATTACK_SCOPE_ENEMY_TEAM_MEMBER = "enemy_team_member"; // Determined by aggro
    const ATTACK_SCOPE_ENEMY_TEAM_MEMBER_LOWEST_VITALITY = "ennemy_team_member_lowest_vitality";
    
    const ATTACK_SCOPE_ALLY_TEAM = "ally_team";
    const ATTACK_SCOPE_ALLY_TEAM_MEMBER = "ally_team_member"; // Determined by aggro
    const ATTACK_SCOPE_ALLY_TEAM_MEMBER_LOWEST_VITALITY = "ally_team_member_lowest_vitality";

    /**
     * Calculate the damage for a physical Attack.
     */
    public static function physicalDamage($atkPower, $strength): float
    {
        return $atkPower * ($strength / 5) * 1.25;
    }

    /**
     * Calculate the damage for a special Attack.
     */
    public static function specialDamage($atkPower, $power): float
    {
        return $atkPower * ($power / 5) * 1.25;
    }

    /**
     * Calculate the damage received from a physical attack
     */
    public static function physicalDefense($damage, $level, $stamina): float
    {
        return $damage / (($level / 2) + ($stamina / 8));
    }

    /**
     * Calculate the damage received from a special attack
     */
    public static function specialDefense($damage, $level, $bravery): float
    {
        return $damage / (($level / 2) + ($bravery / 8));
    }

    /**
     * Calculate if the character has made a crit on his attack or not.
     */
    public static function hasCrit($attackerCoordination, $targetLevel, $targetAgility): bool
    {
        $critChance = $attackerCoordination * 2 / (($targetLevel + $targetAgility) / 2.75);

        if($critChance > 50){
            $critChance = 50;
        }

        $diceResult = rand(1, 100);

        if($diceResult <= $critChance){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Calculate if the character has dodged the attack, or not.
     */
    public static function hasDodged($targetAgility, $attackerLevel, $attackerCoordination): bool
    {
        $dodgeChance = $targetAgility * 2 / (($attackerLevel + $attackerCoordination) / 2.75);

        if($dodgeChance > 50){
            $dodgeChance = 50;
        }

        $diceResult = rand(1, 100);

        if($diceResult <= $dodgeChance){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Verify if the character has stab with the given attack
     */
    public static function hasStab(array $types, string $attackName, AttackRepository $attackRepository): bool
    {
        $attack = $attackRepository->findOneBy(['name' => $attackName]);
        $hasStab = false;
        
        foreach ($types as $type) {
            if($type->getName() === $attack->getType()->getName()){
                $hasStab = true;
            }
        }

        return $hasStab;
    }

    /**
     * Returns one or multiple logs related to an attack
     */
    public function createAttackLog(string $attackName, ?int $damageTaken, bool $hasCrit, ?bool $isCibleKO, CharacterFighter &$lanceur, CharacterFighter &$cible, $status = null){
        $allLogs = [];

        if($hasCrit){
            $allLogs[] = "{$lanceur->getName()} lance {$attackName} !" . " Critique !";
        }
        else {
            $allLogs[] = "{$lanceur->getName()} lance {$attackName} !";
        }
        
        if($status !== null){ // Attaques de Status
            switch($status->getStatusType()){
                case StatusInterface::TYPE_BUFF:
                    $allLogs[] = "{$cible->getName()} voit son {$status->getStatToModify()} augmenter de {$status->getValue()} pendant {$status->getRemainingTurns()} tours.";
                    break;
                case StatusInterface::TYPE_NERF:
                    $allLogs[] = "{$cible->getName()} voit son {$status->getStatToModify()} baisser de {$status->getValue()} pendant {$status->getRemainingTurns()} tours.";
                    break;
            }
           
        }
        else { // Attaques Normales
            if($damageTaken === null){
                $allLogs[] = "{$cible->getName()} a esquivé l'attaque !";
            }
            else {
                $allLogs[] = "{$cible->getName()} subit {$damageTaken} points de dégâts.";
            }
    
            if($isCibleKO){
                $allLogs[] = "{$cible->getName()} est tombé K.O.";
            }
            else {
                $allLogs[] = "{$cible->getName()} a {$cible->getCurrentVitality()} HP restant.";
            }    
        }
        
        return $allLogs;
    }
}