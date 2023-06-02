<?php

namespace App\Service\Combat;

use App\Entity\Species;
use App\Repository\AttackRepository;
use App\Service\Attacks\AbstractAttacks;
use App\Service\Attacks\ExplorerAttacks;
use App\Service\Combat\Status\StatModifierStatus;
use App\Service\Combat\Status\StatusInterface;
use Doctrine\ORM\PersistentCollection;

class CharacterFighter extends ExplorerAttacks
{
    /** Number of preparation in order to play */
    const preparationNeededToPlay = 200;

    const MAX_ACTION_PER_ROTATION = 5;

    /**
     * Base Character Properties
     */
    private int $id;
    private string $name;
    private string $gender;
    private int $level;
    private Species $species;
    private array $types;
    public array $turnLog;

    /**
     * Combat Character Properties
     */
    private $openerRotation;
    private ?int $currentOpenerRotationNumber = 1;
    private $rotation;
    private ?int $currentRotationNumber = 1;
    private float $aggro;
    private int $team;
    private int $vitality;
    private int $strength;
    private int $stamina;
    private int $power;
    private int $bravery;
    private int $presence;
    private int $impassiveness;
    private int $agility;
    private int $coordination;
    private int $speed;
    private int $actionPoint;

    /**
     * Character changing Properties
     */
    private int $currentVitality;
    private int $currentPreparation = 0;
    private bool $isKO = false;
    private array $damagingStatus = [];
    private array $healingStatus = [];
    private array $controlStatus = [];
    private array $buffsStatus = [];
    private array $nerfsStatus = [];

    /**
     * Repositories
     */
    private $attackRepository;

    public function __construct(int $id, string $name, string $gender, int $level, Species $species, array $types, int $team, AttackRepository $attackRepository)
    {
        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->level = $level;
        $this->species = $species;
        $this->types = $types;
        $this->team = $team;
        $this->attackRepository = $attackRepository;
    }

    /**
     * Base Character Properties Getters
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getSpecies(): Species
    {
        return $this->species;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Combat Character Properties Getters
     */
    public function getTotalVitality(): int
    {
        return $this->minValueOf1($this->vitality + $this->getTotalStatusStatChanges('vitality'));
    }

    public function getTotalStrength(): int
    {
        return $this->minValueOf1($this->strength + $this->getTotalStatusStatChanges('strength'));
    }

    public function getTotalStamina(): int
    {
        return $this->minValueOf1($this->stamina + $this->getTotalStatusStatChanges('stamina'));
    }

    public function getTotalPower(): int
    {
        return $this->minValueOf1($this->power + $this->getTotalStatusStatChanges('power'));
    }

    public function getTotalBravery(): int
    {
        return $this->minValueOf1($this->bravery + $this->getTotalStatusStatChanges('bravery'));
    }

    public function getTotalPresence(): int
    {
        return $this->minValueOf1($this->presence + $this->getTotalStatusStatChanges('Presence'));
    }

    public function getTotalImpassiveness(): int
    {
        return $this->minValueOf1($this->impassiveness + $this->getTotalStatusStatChanges('impassiveness'));
    }

    public function getTotalAgility(): int
    {
        return $this->minValueOf1($this->agility + $this->getTotalStatusStatChanges('agility'));
    }

    public function getTotalCoordination(): int
    {
        return $this->minValueOf1($this->coordination + $this->getTotalStatusStatChanges('coordination'));
    }

    public function getTotalSpeed(): int
    {
        return $this->minValueOf1($this->speed + $this->getTotalStatusStatChanges('speed'));
    }

    public function getTotalActionPoint(): int
    {
        return $this->minValueOf1($this->actionPoint + $this->getTotalStatusStatChanges('actionPoint'));
    }

    /**
     * Initiate all Combat stats of the character
     */
    public function initiateCombatProperties($openerRotation, $rotation, $stats): void
    {
        $this->openerRotation = $openerRotation;
        $this->rotation = $rotation;

        $this->vitality = $stats->getVitality();
        $this->currentVitality = $this->vitality;
        $this->strength = $stats->getStrength();
        $this->stamina = $stats->getStamina();
        $this->power = $stats->getPower();
        $this->bravery = $stats->getBravery();
        $this->presence = $stats->getPresence();
        $this->impassiveness = $stats->getImpassiveness();
        $this->agility = $stats->getAgility();
        $this->coordination = $stats->getCoordination();
        $this->speed = $stats->getSpeed();
        $this->actionPoint = $stats->getActionPoint();
        
        $this->aggro = $this->generateAggro();
    }

    /**
     * Plays the turn of the character, if he has enough preparation for it.
     */
    public function playTurn(&$enemyTeamCharacterFighters, &$allyTeamCharacterFighters): array
    {
        $this->currentPreparation -= self::preparationNeededToPlay;

        $nextMove = $this->getNextMove();
        $target = $this->getTarget($nextMove, $enemyTeamCharacterFighters, $allyTeamCharacterFighters);
        $turnLog = $this->useAttack($nextMove, $target);
        
        $this->manageAndRemoveOldStatus(); 

        return $turnLog;
    }

    /**
     * Use the attack for the character
     */
    public function useAttack($nextMove, $target): array
    {
        $hasCrit = $this->hasCrit($this->getTotalCoordination(), $target->getLevel(), $target->getTotalAgility());
        $hasDodged = $this->hasDodged($target->getTotalAgility(), $this->getLevel(), $this->getTotalCoordination());
        $hasStab = $this->hasStab($this->getTypes(), $nextMove, $this->attackRepository);

        return $this->$nextMove($this, $target, $hasCrit, $hasDodged, $hasStab);
    }

    public function minValueOf1($value): int
    {
        if($value < 1){
            return 1;
        }

        return $value;
    }

    public function getTeam(): int
    {
        return $this->team;
    }

    public function getAggro(): float
    {
        return $this->aggro;
    }

    /**
     * Generate the ammount of aggro the character has
     */
    public function generateAggro(): float
    {
        $aggroAmount = 0;

        $aggroAmount += $this->level / 2;
        $aggroAmount += $this->vitality / 8;
        $aggroAmount += $this->stamina / 8;
        $aggroAmount += $this->bravery / 8;
        $aggroAmount += $this->impassiveness / 9;
        $aggroAmount += $this->presence / 9;
        $aggroAmount += $this->strength / 10;
        $aggroAmount += $this->power / 10;
        $aggroAmount += $this->agility / 10;
        $aggroAmount += $this->coordination / 10;
        $aggroAmount += $this->speed / 10;

        return $aggroAmount;
    }

    /**
     * Changing Character Properties Getters
     */
    public function getCurrentVitality(): int
    {
        return $this->currentVitality;
    }

    /**
     * Returns true if the Character is full Vitality
     */
    public function isFullVitality(): bool
    {
        if($this->getCurrentVitality() >= $this->getTotalVitality()){
            return true;
        }

        return false;
    }

    /**
     * Makes the character take damage and returns true if K.O
     * 
     * TODO : Should return damage amount taken and manage damage reduction
     */
    public function takeDamage(int $damage): bool
    {
        if($this->isKO === false){
            $this->currentVitality = $this->currentVitality - $damage;

            if($this->isKo()){
                return true;
            }
        }

        return false;
    }

    /**
     * Makes the character receive heal and returns
     * 
     * TODO : Should return amount healed
     */
    public function receiveHeal(int $heal): void
    {
        $this->currentVitality = $this->currentVitality + $heal;

        if($this->isFullVitality()){
            $this->currentVitality = $this->getTotalVitality();
        }
    }

    public function prepare(): void
    {
        $this->currentPreparation += 10 + ceil($this->getTotalSpeed() / 3);
    }

    /**
     * Returns true if the character is K.O
     */
    public function isKo(): bool
    {
        if($this->currentVitality < 1){
            $this->isKO = true;
            $this->currentVitality = 0;
            
            return true;
        }

        return false;
    }

    /**
     * Returns true if the character is prepared
     */
    public function isPrepared(): bool
    {
        if($this->currentPreparation < self::preparationNeededToPlay){
            return false;
        }

        return true;
    }

    /**
     * Returns the modifier value of a Stat with buff and nerf status applied
     */
    private function getTotalStatusStatChanges($statName): int
    {
        $totalNerfValue = 0;
        $totalBuffValue = 0;

        foreach($this->nerfsStatus as $nerfStatus){
            if($nerfStatus->getStatToModify() === $statName){
                $totalNerfValue -= $nerfStatus->getValue();    
            }
        }

        foreach($this->buffsStatus as $buffStatus){
            if($buffStatus->getStatToModify() === $statName){
                
                $totalBuffValue =+ $buffStatus->getValue();
            }
        }

        return $totalBuffValue + $totalNerfValue;
    }

    /**
     * Manage turns of status, and remove the old ones that dont do anything anymore.
     */
    private function manageAndRemoveOldStatus(): void
    {
        foreach ($this->buffsStatus as $key => $buffStatus) {
            $buffStatus->useTurn();

            // Removes old status
            if($buffStatus->getRemainingTurns() === null){
                unset($this->buffsStatus[$key]);
            }
        }

        foreach ($this->nerfsStatus as $key => $nerfStatus) {
            $nerfStatus->useTurn();

            // Remove old status
            if($nerfStatus->getRemainingTurns() === null){
                unset($this->nerfsStatus[$key]);
            }
        }
    }

    /**
     * Returns the next move for the character to use
     */
    private function getNextMove(): string
    {
        $nextMove = null;

        if($this->currentOpenerRotationNumber === null){ // Rotation
            switch ($this->currentRotationNumber){
                case 1:
                    $nextMove = $this->rotation->getAttackOne()->getName();
                    break;
                case 2:
                    $nextMove = $this->rotation->getAttackTwo()->getName();
                    break;
                case 3:
                    $nextMove = $this->rotation->getAttackThree()->getName();
                    break;
                case 4:
                    $nextMove = $this->rotation->getAttackFour()->getName();
                    break;
                case 5:
                    $nextMove = $this->rotation->getAttackFive()->getName();
                    break;
            }
            $this->currentRotationNumber++;

            if($this->currentRotationNumber > self::MAX_ACTION_PER_ROTATION){
                $this->currentRotationNumber = 1;
            }
        }
        else { // Opener
            switch ($this->currentOpenerRotationNumber){
                case 1:
                    $nextMove = $this->openerRotation->getAttackOne()->getName();
                    break;
                case 2:
                    $nextMove = $this->openerRotation->getAttackTwo()->getName();
                    break;
                case 3:
                    $nextMove = $this->openerRotation->getAttackThree()->getName();
                    break;
                case 4:
                    $nextMove = $this->openerRotation->getAttackFour()->getName();
                    break;
                case 5:
                    $nextMove = $this->openerRotation->getAttackFive()->getName();
                    break;
            }

            $this->currentOpenerRotationNumber++;
            
            if($this->currentOpenerRotationNumber > self::MAX_ACTION_PER_ROTATION){
                $this->currentOpenerRotationNumber = null;
            }
        }

        return $nextMove;
    }

    /**
     * Returns the target of the character
     */
    private function getTarget($nextMove, $enemyTeamCharacterFighters, $allyTeamCharacterFighters): array | CharacterFighter
    {
        $attack = $this->attackRepository->findOneBy(['name' => $nextMove]);

        $target = null;

        switch ($attack->getScope()) {
            case AbstractAttacks::ATTACK_SCOPE_SELF:
                $target = $this;
                break;
            case AbstractAttacks::ATTACK_SCOPE_ENEMY_TEAM:
                $target = $enemyTeamCharacterFighters;
                break;
            case AbstractAttacks::ATTACK_SCOPE_ENEMY_TEAM_MEMBER:
                usort($enemyTeamCharacterFighters, function($a, $b){
                    return $a->getAggro() <=> $b->getAggro();
                });

                $target = $enemyTeamCharacterFighters[0];
                break;
            case AbstractAttacks::ATTACK_SCOPE_ENEMY_TEAM_MEMBER_LOWEST_VITALITY:
                $notFullVitalityEnemyTeamMembers = [];

                foreach ($enemyTeamCharacterFighters as $enemy) {
                    if(!$enemy->isFullVitality()){
                        $notFullVitalityEnemyTeamMembers[] = $enemy;
                    }
                }

                if(empty($notFullVitalityEnemyTeamMembers)){
                    usort($enemyTeamCharacterFighters, function($a, $b){
                        return $b->getCurrentVitality() <=> $a->getCurrentVitality();
                    });
    
                    $target = $enemyTeamCharacterFighters[0];
                }
                else {
                    usort($notFullVitalityEnemyTeamMembers, function($a, $b){
                        return $b->getCurrentVitality() <=> $a->getCurrentVitality();
                    });
    
                    $target = $notFullVitalityEnemyTeamMembers[0];
                }
                break;
            case AbstractAttacks::ATTACK_SCOPE_ALLY_TEAM:
                $target = $allyTeamCharacterFighters;
                break;
            case AbstractAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER:
                usort($allyTeamCharacterFighters, function($a, $b){
                    return $a->getAggro() <=> $b->getAggro();
                });

                $target = $allyTeamCharacterFighters[0];
                break;
            case AbstractAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER_LOWEST_VITALITY:
                $notFullVitalityAllyTeamMembers = [];
                
                foreach ($allyTeamCharacterFighters as $ally) {
                    if(!$ally->isFullVitality()){
                        $notFullVitalityAllyTeamMembers[] = $ally;
                    }
                }

                if(empty($notFullVitalityAllyTeamMembers)){
                    usort($allyTeamCharacterFighters, function($a, $b){
                        return $b->getCurrentVitality() <=> $a->getCurrentVitality();
                    });
    
                    $target = $allyTeamCharacterFighters[0];
                }
                else {
                    usort($notFullVitalityAllyTeamMembers, function($a, $b){
                        return $b->getCurrentVitality() <=> $a->getCurrentVitality();
                    });
    
                    $target = $notFullVitalityAllyTeamMembers[0];
                }
                break;
        }

        return $target;
    }

    /**
     * Add a status to the character
     */
    public function addStatus(StatusInterface $status): void
    {
        switch($status->getStatusType()){
            case StatusInterface::TYPE_DAMAGING:
                $this->damagingStatus[] = $status;
                break;
            case StatusInterface::TYPE_HEALING:
                $this->healingStatus[] = $status;
                break;
            case StatusInterface::TYPE_CONTROL:
                $this->controlStatus[] = $status;
                break;
            case StatusInterface::TYPE_BUFF:
                $this->buffsStatus[] = $status;
                break;
            case StatusInterface::TYPE_NERF:
                $this->nerfsStatus[] = $status;
                break;

        }
    }
}