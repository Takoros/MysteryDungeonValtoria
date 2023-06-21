<?php

namespace App\Service\Attack;

use App\Entity\Attack;

class FighterAttacks
{
    use BugAttacks;
    use PoisonAttacks;
    use GhostAttacks;
    use DarkAttacks;
    use FlyingAttacks;
    use SteelAttacks;
    use GroundAttacks;
    use RockAttacks;
    use WaterAttacks;
    use FireAttacks;
    use ExplorerAttacks;
    use GrassAttacks;
    use ElectricAttacks;
    use NormalAttacks;
    use IceAttacks;
    use FightingAttacks;
    use PsyAttacks;
    use FairyAttacks;
    use DragonAttacks;

    const STAB_DAMAGE = 1.25; // Dégâts du STAB

    const ATTACK_SCOPE_SELF = "self"; // Soi-même / Lanceur

    const ATTACK_SCOPE_ENEMY_TEAM = "enemy_team"; // Équipe Adverse
    const ATTACK_SCOPE_ENEMY_TEAM_MEMBER = "enemy_team_member"; // Membre de l'équipe adverse
    const ATTACK_SCOPE_ENEMY_TEAM_MEMBER_LOWEST_VITALITY = "ennemy_team_member_lowest_vitality"; // Membre de l'équipe adverse avec le moins de vitalité

    const ATTACK_SCOPE_ALLY_TEAM = "ally_team"; // Équipe alliée 
    const ATTACK_SCOPE_ALLY_TEAM_MEMBER = "ally_team_member"; // Membre de l'équipe alliée
    const ATTACK_SCOPE_ALLY_TEAM_MEMBER_LOWEST_VITALITY = "ally_team_member_lowest_vitality"; // Membre de l'équipe alliée avec le moins de vitalité
    
    public function __construct() {
        $this->loadAbstractAttacks();

        $this->loadBugAttacks();
        $this->loadPoisonAttacks();
        $this->loadGhostAttacks();
        $this->loadDarkAttacks();
        $this->loadFlyingAttacks();
        $this->loadSteelAttacks();
        $this->loadGroundAttacks();
        $this->loadRockAttacks();
        $this->loadWaterAttacks();
        $this->loadFireAttacks();
        $this->loadExplorerAttacks();
        $this->loadGrassAttacks();
        $this->loadElectricAttacks();
        $this->loadNormalAttacks();
        $this->loadIceAttacks();
        $this->loadFightingAttacks();
        $this->loadPsyAttacks();
        $this->loadFairyAttacks();
        $this->loadDragonAttacks();
    }
}