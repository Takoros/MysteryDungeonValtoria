<?php

namespace App\Service\Combat;

use App\Entity\Attack;
use App\Entity\Character;
use App\Entity\CombatLog;
use App\Entity\Rotation;
use App\Entity\Species;
use App\Service\Combat\Attack\FighterAttacks;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatusInterface;
use App\Service\Dungeon\MonsterCharacter;

/**
 * Class used in Arena as fighters for the combat
 */
class Fighter extends FighterAttacks
{
    /** Number of preparation in order to play */
    const preparationNeededToPlay = 200;

    /**
     * Base Fighter Properties
     */
    private int|string $id;
    private string $name;
    private string $gender;
    private int $level;
    private Species $Species;
    private array $types;
    private string $pronoun; 

    /**
     * Combat Fighter Properties
     */
    private Rotation $rotation;
    private Rotation $openerRotation;
    private ?int $currentOpenerRotationNumber = 1;
    private ?int $currentRotationNumber = 1;
    private float $aggro;
    private int $team;
    private bool $isKO = false;

    /**
     * Statistic Fighter Properties
     */
    private int $currentVitality;
    private int $currentPreparation = 0;
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

    /**
     * Status Fighter Properties
     */
    private array $damagingStatus = [];
    private array $healingStatus = [];
    private array $controlStatus = [];
    private array $buffStatus = [];
    private array $nerfStatus = [];
    private array $nextMultipleStepAttack = [];

    /**
     * Access Fighter Properties
     */
    public static Arena $arena;
    public static CombatLog $combatLog;

    public function __construct(Character|MonsterCharacter $character, int $team, Arena &$arena, CombatLog &$combatLog)
    {
        // Base Fighter Properties
        $this->id = $character->getId();
        $this->name = $character->getName();
        $this->gender = $character->getGender();
        $this->level = $character->getLevel();
        $this->Species = $character->getSpecies();
        $this->types = $character->getTypes();

        if($this->gender === Character::GENDER_MALE){
            $this->pronoun = 'il';
        }
        else if($this->gender === Character::GENDER_FEMALE){
            $this->pronoun = 'elle';
        }

        // Combat Fighter Properties
        $this->openerRotation = $character->getOpenerRotation();
        $this->rotation = $character->getRotation();
        $this->team = $team;

        // Statistic Fighter Properties
        $stats = $character->getStats();

        $this->currentVitality = $this->vitality = $stats->getVitality();
        $this->strength = $stats->getStrength();
        $this->stamina = $stats->getStamina();
        $this->power = $stats->getPower();
        $this->bravery = $stats->getBravery();
        $this->presence = $stats->getPresence();
        $this->impassiveness = $stats->getImpassiveness();
        $this->agility = $stats->getAgility();
        $this->coordination = $stats->getCoordination();
        $this->speed = $stats->getSpeed();

        // Access Fighter Properties
        $this::$arena = $arena;
        $this::$combatLog = $combatLog;

        parent::__construct();
        $this->aggro = $this->generateAggro();
    }

    /**
     * Base Fighter Getters
     */

    public function getId(): int|string
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
        return $this->Species;
    }

    public function getTypes(): array
    {
        return $this->types;
    }
    
    /**
     * Combat Fighter Getters
     */

    public function getRotation(): Rotation
    {
        return $this->rotation;
    }

    public function getOpenerRotation(): Rotation
    {
        return $this->openerRotation;
    }

    public function getCurrentOpenerRotationNumber(): ?int
    {
        return $this->currentOpenerRotationNumber;
    }

    public function getCurrentRotationNumber(): ?int
    {
        return $this->currentRotationNumber;
    }

    public function getAggro(): float
    {
        return $this->aggro;
    }

    public function getTeam(): int
    {
        return $this->team;
    }

    /**
     * Returns true if the Fighter is KO
     */
    public function isKO(): bool
    {
        return $this->isKO;
    }

    /**
     * Statistic Fighter Getters
     */
    public function setCurrentVitality(int $value): self
    {
        $this->currentVitality = $value;

        return $this;
    }

    public function getCurrentVitality(): int 
    {
        return $this->currentVitality;
    }

    /**
     * Returns true if the Fighter is prepared
     */
    public function isPrepared(): bool
    {
        if($this->currentPreparation < self::preparationNeededToPlay){
            return false;
        }

        return true;
    }

    public function getVitality(): int
    {
        return $this->minValueOfOne($this->vitality + $this->getStatisticModifier('vitality'));
    }

    public function getBaseVitality(): int
    {
        return $this->vitality;
    }

    public function getStrength(): int
    {
        return $this->minValueOfOne($this->strength + $this->getStatisticModifier('strength'));
    }

    public function getBaseStrength(): int
    {
        return $this->strength;
    }

    public function getStamina(): int
    {
        return $this->minValueOfOne($this->stamina + $this->getStatisticModifier('stamina'));
    }

    public function getBaseStamina(): int
    {
        return $this->stamina;
    }

    public function getPower(): int
    {
        return $this->minValueOfOne($this->power + $this->getStatisticModifier('power'));
    }

    public function getBasePower(): int
    {
        return $this->power;
    }

    public function getBravery(): int
    {
        return $this->minValueOfOne($this->bravery + $this->getStatisticModifier('bravery'));
    }

    public function getBaseBravery(): int
    {
        return $this->bravery;
    }

    public function getPresence(): int
    {
        return $this->minValueOfOne($this->presence + $this->getStatisticModifier('presence'));
    }

    public function getBasePresence(): int
    {
        return $this->presence;
    }

    public function getImpassiveness(): int
    {
        return $this->minValueOfOne($this->impassiveness + $this->getStatisticModifier('impassiveness'));
    }

    public function getBaseImpassiveness(): int
    {
        return $this->impassiveness;
    }

    public function getAgility(): int
    {
        return $this->minValueOfOne($this->agility + $this->getStatisticModifier('agility'));
    }

    public function getBaseAgility(): int
    {
        return $this->agility;
    }

    public function getCoordination(): int
    {
        return $this->minValueOfOne($this->coordination + $this->getStatisticModifier('coordination'));
    }

    public function getBaseCoordination(): int
    {
        return $this->coordination;
    }

    public function getSpeed(): int
    {
        return $this->minValueOfOne($this->speed + $this->getStatisticModifier('speed'));
    }

    public function getBaseSpeed(): int
    {
        return $this->speed;
    }

    /**
     * Returns true if the Fighter is full Vitality
     */
    public function isFullVitality(): bool
    {
        if($this->getCurrentVitality() >= $this->getVitality()){
            return true;
        }

        return false;
    }

    /**
     * Returns the total of a statistic with status applied
     */
    public function getStatisticTotal(string $statistic): int
    {
        return $this->minValueOfOne($this->$statistic + $this->getStatisticModifier($statistic));
    }

    /**
     * Returns the modifier value of a Statistic
     */
    private function getStatisticModifier(string $statisticName): int
    {
        $totalNerfModifier = 0;
        $totalBuffModifier = 0;

        foreach ($this->nerfStatus as $nerf) {
            if($nerf->getStatisticModified() === $statisticName){
                $totalNerfModifier -= $nerf->getModifier();
            }
        }

        foreach ($this->buffStatus as $buff) {
            if($buff->getStatisticModified() === $statisticName){
                $totalBuffModifier += $buff->getModifier();
            }
        }

        // Burn Reduce Strength
        if($statisticName === 'strength'){
            foreach ($this->damagingStatus as $status) {
                if($status->getStatusType() === StatusInterface::TYPE_DAMAGING_BURN){
                    $totalNerfModifier = $status->getStatusEffectValue($this);
                }
            }
        }

        return $totalNerfModifier + $totalBuffModifier;
    }

    /**
     * Returns the value, but if its less than one, return one.
     */
    public static function minValueOfOne(int $value): int
    {
        if($value < 1){
            return 1;
        }

        return $value;
    }

    /**
     * Status Fighter Getters
     */

    public function getDamagingStatus(): array
    {
        return $this->damagingStatus;
    }

    public function getHealingStatus(): array
    {
        return $this->healingStatus;
    }

    public function getControlStatus(): array
    {
        return $this->controlStatus;
    }

    public function getBuffStatus(): array
    {
        return $this->buffStatus;
    }

    public function getNerfStatus(): array
    {
        return $this->nerfStatus;
    }

    /**
     * Returns the amount of control Status that currently affects the Fighter
     */
    public function getNumberOfCurrentControlStatus()
    {
        return count($this->controlStatus);
    }

    /**
     * Returns true if the Fighter is affected by a status of this type
     */
    public function isAffectedByStatus(string $type): bool
    {
        $isAffected = false;

        $allStatus = array_merge($this->damagingStatus, $this->controlStatus);

        foreach ($allStatus as $status) {
            if($status->getStatusType() === $type){
                $isAffected = true;
                break;
            }
        }

        return $isAffected;
    }

    /**
     * Returns an array of all the negative status affecting the fighter
     */
    public function getAllNegativeStatus(): array
    {
        return array_merge($this->getControlStatus(), $this->getDamagingStatus(), $this->getNerfStatus());
    }

    /**
     * Returns an array of all the positive status affecting the fighter
     */
    public function getAllPositiveStatus(): array
    {
        return array_merge($this->getHealingStatus(), $this->getBuffStatus());
    }

    /**
     * Generate the ammount of aggro the Fighter has
     */
    private function generateAggro(): float
    {
        $aggroAmount = 0;

        $aggroAmount += $this->level / 2;
        $aggroAmount += $this->vitality / 7;
        $aggroAmount += $this->stamina / 7;
        $aggroAmount += $this->bravery / 7;
        $aggroAmount += $this->impassiveness / 9;
        $aggroAmount += $this->presence / 9;
        $aggroAmount += $this->strength / 10;
        $aggroAmount += $this->power / 10;
        $aggroAmount += $this->agility / 10;
        $aggroAmount += $this->coordination / 10;
        $aggroAmount += $this->speed / 10;

        return $aggroAmount;
    }

    public function getNextMultipleStepAttack(): array
    {
        return $this->nextMultipleStepAttack;
    }

    public function setNextMultipleStepAttack($value): self
    {
        $this->nextMultipleStepAttack = $value;

        return $this;
    }

    public function resetNextMultipleStepAttack(): self
    {
        $this->nextMultipleStepAttack = [];
        
        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                              COMBAT FUNCTIONS                              */
    /* -------------------------------------------------------------------------- */

    /**
     * Plays the turn of the fighter, if he has enough preparation for it.
     */
    public function playTurn(array &$enemyFighters, array &$allyFighters): void
    {
        $this->currentPreparation -= self::preparationNeededToPlay;

        $nextAttack = $this->getNextAttack();

        $this->manageTurn($nextAttack, $enemyFighters, $allyFighters);

        $this->manageStatus();
    }

    /**
     * Process a normal attack behavior for the fighter
     */
    public function attackNormally(Attack $nextAttack, array $enemyFighters, array $allyFighters): void
    {
        $target = $this->getTarget($nextAttack, $enemyFighters, $allyFighters);

        $nextAttackId = $nextAttack->getId();
        $this->$nextAttackId($this, $target);
    }

    /**
     * Process a self attack behavior caused by a confusion for the Fighter
     */
    public function attackItSelf(): void
    {
        $this->ATTACK_EXPLORER_BASE($this, $this);
    }

    /**
     * Manage how the fighter's turn will be processed depending on what control status affects it
     */
    private function manageTurn(Attack $nextAttack, array &$enemyFighters, array &$allyFighters): void
    {
        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_FREEZE)){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_FREEZE);
            return ;
        }
        
        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_PETRIFICATION)){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_PETRIFICATION);
            return ;
        }
        
        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_SLEEP)){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_SLEEP);
            return ;
        }
        
        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_FATIGUE)){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_FATIGUE);
            return ;
        }
        
        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_PARALYSIS) && ControlStatus::doParalysisActivate()){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_PARALYSIS);
            return ;
        }

        if($this->isAffectedByStatus(StatusInterface::TYPE_CONTROL_CONFUSION) && ControlStatus::doConfusionActivate()){
            $this::$combatLog->addControlStatusActivateLog($this, StatusInterface::TYPE_CONTROL_CONFUSION);
            $this->attackItSelf();
            return ;
        }
        
        $this->attackNormally($nextAttack, $enemyFighters, $allyFighters);
    }

    /**
     * Raises the currentPreparation of the Fighter
     */
    public function prepare(): void
    {
        $this->currentPreparation += 10 + ceil($this->getSpeed() / 3);
    }

    /**
     * Makes the Fighter receive damage and returns the amount of damage received.
     */
    public function receiveDamage(int $totalRawPhysicalDamage, int $totalRawSpecialDamage): int
    {
        if($this->isKO === true){
            return 0;
        }

        $reducedPhysicalDamage = $this->calculatePhysicalDamageReceived($totalRawPhysicalDamage, $this->level, $this->getStamina());
        $reducedSpecialDamage = $this->calculateSpecialDamageReceived($totalRawSpecialDamage, $this->level, $this->getBravery());

        $this->currentVitality -= $reducedPhysicalDamage + $reducedSpecialDamage;
        $this->checkKO();

        $damageReceived = $reducedPhysicalDamage + $reducedSpecialDamage;

        $this::$combatLog->addReceiveDamageLog($this, $reducedPhysicalDamage, $reducedSpecialDamage);

        return $damageReceived;
    }

    /**
     * Makes the fighter receive damage from damagingStatus and returns the amount of damage received.
     */
    public function receiveDamageFromStatus($status): int
    {
        if($this->isKO === true){
            return 0;
        }

        $statusDamage = $status->getStatusDamage($this);
        $this->currentVitality -= $statusDamage;
        $this->checkKO();

        $damageReceived = $statusDamage;

        $this::$combatLog->addReceiveDamageFromStatusLog($this, $damageReceived, $status);

        return $damageReceived;
    }

    /**
     * Makes the Fighter receive healing and returns the amount of heal received.
     */
    public function receiveHealing(int $totalRawHealing): int
    {
        if($this->isFullVitality()){
            return 0;
        }

        $missingVitality = $this->getVitality() - $this->getCurrentVitality();

        if($totalRawHealing > $missingVitality){
            $totalRawHealing = $missingVitality;
        }

        $this->currentVitality += $totalRawHealing;

        $this::$combatLog->addReceiveHealingLog($this, $totalRawHealing);

        return $totalRawHealing;
    }

    /**
     * Verifies if the Fighter is KO and change its properties if so
     */
    public function checkKO(): void
    {
        if($this->currentVitality <= 0){
            $this->isKO = true;
            $this->currentVitality = 0;
        }
    }

    /**
     * Returns the next attack the Fighter is gonna make
     */
    public function getNextAttack(): Attack
    {
        $nextAttack = null;

        if(!empty($this->nextMultipleStepAttack)){
            $nextAttack = $this->nextMultipleStepAttack['Attack'];
            return $nextAttack;
        }

        if($this->currentOpenerRotationNumber === null){ // Rotation
            switch ($this->currentRotationNumber){
                case 1:
                    $nextAttack = $this->rotation->getAttackOne();
                    break;
                case 2:
                    $nextAttack = $this->rotation->getAttackTwo();
                    break;
                case 3:
                    $nextAttack = $this->rotation->getAttackThree();
                    break;
                case 4:
                    $nextAttack = $this->rotation->getAttackFour();
                    break;
                case 5:
                    $nextAttack = $this->rotation->getAttackFive();
                    break;
            }
            $this->currentRotationNumber++;

            if($this->currentRotationNumber > Rotation::MAX_ACTION_PER_ROTATION){
                $this->currentRotationNumber = 1;
            }
        }
        else { // Opener
            switch ($this->currentOpenerRotationNumber){
                case 1:
                    $nextAttack = $this->openerRotation->getAttackOne();
                    break;
                case 2:
                    $nextAttack = $this->openerRotation->getAttackTwo();
                    break;
                case 3:
                    $nextAttack = $this->openerRotation->getAttackThree();
                    break;
                case 4:
                    $nextAttack = $this->openerRotation->getAttackFour();
                    break;
                case 5:
                    $nextAttack = $this->openerRotation->getAttackFive();
                    break;
            }

            $this->currentOpenerRotationNumber++;

            if($this->currentOpenerRotationNumber > Rotation::MAX_ACTION_PER_ROTATION){
                $this->currentOpenerRotationNumber = null;
            }
        }

        return $nextAttack;
    }

     /**
     * Returns the target of the Fighter
     */
    private function getTarget(Attack $attack, array $enemyFighters, array $allyFighters): array | Fighter
    {
        $target = null;

        switch ($attack->getScope()) {
            /**
             * This fighter
             */
            case FighterAttacks::ATTACK_SCOPE_SELF:
                $target = $this;
                break;


            /**
             * Ennemy Team
             */
            case FighterAttacks::ATTACK_SCOPE_ENEMY_TEAM:
                $target = $enemyFighters;
                break;


            /**
             * Ennemy with the greatest aggro
             */
            case FighterAttacks::ATTACK_SCOPE_ENEMY_TEAM_MEMBER:
                usort($enemyFighters, function($a, $b){
                    return $b->getAggro() <=> $a->getAggro();
                });

                $target = $enemyFighters[0];
                break;


            /**
             * Ennemy with the lowest current vitality
             */
            case FighterAttacks::ATTACK_SCOPE_ENEMY_TEAM_MEMBER_LOWEST_VITALITY:
                /* - Verifying that at least one ennemy has lost vitality - */
                $notFullVitalityEnemyTeamMembers = [];

                foreach ($enemyFighters as $enemy) {
                    if(!$enemy->isFullVitality()){
                        $notFullVitalityEnemyTeamMembers[] = $enemy;
                    }
                }

                if(empty($notFullVitalityEnemyTeamMembers)){
                    usort($enemyFighters, function($a, $b){
                        return $a->getCurrentVitality() <=> $b->getCurrentVitality();
                    });

                    $target = $enemyFighters[0];
                }
                else {
                    usort($notFullVitalityEnemyTeamMembers, function($a, $b){
                        return $a->getCurrentVitality() <=> $b->getCurrentVitality();
                    });

                    $target = $notFullVitalityEnemyTeamMembers[0];
                }
                break;


            /**
             * Ally Team
             */
            case FighterAttacks::ATTACK_SCOPE_ALLY_TEAM:
                $target = $allyFighters;
                break;


            /**
             * Ally with the greatest aggro
             */
            case FighterAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER:
                usort($allyFighters, function($a, $b){
                    return $b->getAggro() <=> $a->getAggro();
                });

                $target = $allyFighters[0];
                break;


            /**
             * Ally with the lowest current vitality
             */
            case FighterAttacks::ATTACK_SCOPE_ALLY_TEAM_MEMBER_LOWEST_VITALITY:
                /* - Verifying that at least one ally has lost vitality - */
                $notFullVitalityAllyTeamMembers = [];

                foreach ($allyFighters as $ally) {
                    if(!$ally->isFullVitality()){
                        $notFullVitalityAllyTeamMembers[] = $ally;
                    }
                }

                if(empty($notFullVitalityAllyTeamMembers)){
                    usort($allyFighters, function($a, $b){
                        return $a->getCurrentVitality() <=> $b->getCurrentVitality();
                    });

                    $target = $allyFighters[0];
                }
                else {
                    usort($notFullVitalityAllyTeamMembers, function($a, $b){
                        return $a->getCurrentVitality() <=> $b->getCurrentVitality();
                    });

                    $target = $notFullVitalityAllyTeamMembers[0];
                }
                break;
        }

        return $target;
    }

    /**
     * Add a status to the Fighter
     */
    public function addStatus(mixed $status): void
    {
        if($status->getStatusType() === StatusInterface::TYPE_DAMAGING_BURN 
        || $status->getStatusType() === StatusInterface::TYPE_DAMAGING_POISON
        || $status->getStatusType() === StatusInterface::TYPE_DAMAGING_BAD_POISON)
        {
            $this::$combatLog->addDamagingStatusReceivedLog($this, $status);
            $this->damagingStatus[] = $status;
            return;
        }

        if($status->getStatusType() === StatusInterface::TYPE_CONTROL_CONFUSION
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_FATIGUE
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_FREEZE
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_PARALYSIS
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_PETRIFICATION
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_SLEEP
        || $status->getStatusType() === StatusInterface::TYPE_CONTROL_YAWN
        ){
            
            if($this->isAffectedByStatus($status->getStatusType())){
                $this::$combatLog->addControlStatusNoEffect($this, $status->getStatusType());
                return;
            }
            
            $this::$combatLog->addControlStatusReceivedLog($this, $status);
            $this->controlStatus[] = $status;
            return;
        }

        if($status->getStatusType() === StatusInterface::TYPE_BUFF){
            $this->buffStatus[] = $status;
            $this::$combatLog->addStatisticModifierStatusReceivedLog($this, $status);
            return;
        }

        if($status->getStatusType() === StatusInterface::TYPE_NERF){
            $status->setModifierAfterReduction($this->calculateNerfValueReceived($status->getModifier(), $this->getLevel(), $this->getImpassiveness()));

            $this->nerfStatus[] = $status;
            $this::$combatLog->addStatisticModifierStatusReceivedLog($this, $status);
            return;
        }

        if($status->getStatusType() === StatusInterface::TYPE_HEALING){
            $this->healingStatus[] = $status;
            return;
        }
    }

    /**
     * Purge all buff or nerf of a statistic
     */
    public function purgeAllModifierOfStatistic(string $statistic, string $type): void
    {
        if($type === StatusInterface::TYPE_BUFF){
            foreach ($this->buffStatus as $status) {
                if($status->getStatisticModified() === $statistic){
                    $status->getPurged($this);
                }
            }
        }
        else if ($type === StatusInterface::TYPE_NERF){
            foreach ($this->nerfStatus as $status) {
                if($status->getStatisticModified() === $statistic){
                    $status->getPurged($this);
                }
            }
        }

        $this->removeObsoleteStatus();
    }

    private function manageStatus(): void
    {
        $allStatus = array_merge($this->buffStatus, $this->nerfStatus, $this->controlStatus, $this->damagingStatus, $this->healingStatus);

        foreach ($allStatus as $status) {
            $status->manageStatus($this);
        }

        $this->removeObsoleteStatus();
    }

    /**
     * Remove all obselete status
     */
    public function removeObsoleteStatus(): void
    {
        foreach ($this->buffStatus as $key => $buffStatus) {
            if($buffStatus->getRemainingTurns() === null){
                unset($this->buffStatus[$key]);
            }
        }

        foreach ($this->nerfStatus as $key => $nerfStatus) {
            if($nerfStatus->getRemainingTurns() === null){
                unset($this->nerfStatus[$key]);
            }
        }

        foreach ($this->controlStatus as $key => $controlStatus) {
            if($controlStatus->getRemainingTurns() === null){
                unset($this->controlStatus[$key]);
            }
        }

        foreach ($this->healingStatus as $key => $healingStatus) {
            if($healingStatus->getRemainingTurns() === null){
                unset($this->healingStatus[$key]);
            }
        }

        foreach ($this->damagingStatus as $key => $damagingStatus) {
            if($damagingStatus->getRemainingTurns() === null){
                unset($this->damagingStatus[$key]);
            }
        }
    }
}