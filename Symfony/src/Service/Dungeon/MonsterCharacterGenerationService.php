<?php

namespace App\Service\Dungeon;

use App\Entity\Attack;
use App\Entity\Dungeon;
use App\Entity\Rotation;
use App\Entity\Stats;
use App\Repository\AttackRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class MonsterCharacterGenerationService
{
    private $speciesRepository;
    private $typeRepository;
    private $attackRepository;
    private $translator;
 
    public function __construct(SpeciesRepository $speciesRepository, TypeRepository $typeRepository, AttackRepository $attackRepository, TranslatorInterface $translator) {
        $this->speciesRepository = $speciesRepository;
        $this->typeRepository = $typeRepository;
        $this->attackRepository = $attackRepository;
        $this->translator = $translator;
    }

    public function generateMonstersForTile(array $monstersList, Dungeon $dungeon): array
    {
        $lettersList = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P"];
        $monstersSpeciesCount = [];
        $monsterIdCount = 0;
        $monsterList = [];

        foreach ($monstersList as $monsterSpeciesName) {
            $monsterIdCount++;
            if(!array_key_exists($monsterSpeciesName, $monstersSpeciesCount)){
                $monstersSpeciesCount[$monsterSpeciesName] = 0;
            }
            
            $monstersSpeciesCount[$monsterSpeciesName]++;

            if($monstersSpeciesCount[$monsterSpeciesName] > 1){
                $monsterName = $monsterSpeciesName . "[{$lettersList[$monstersSpeciesCount[$monsterSpeciesName]-1]}]";
            }
            else {
                $monsterName = $monsterSpeciesName;
            }

            $monsterSpecies = $this->speciesRepository->findOneBy(['name' => $monsterSpeciesName]);
            
            $monster = new MonsterCharacter($dungeon->getMinMonsterLevel(), $dungeon->getMaxMonsterLevel());
            $monsterStats = $this->generateStatsForLevel($monster->getLevel(), $monster->monsterType, $monster);
            
            $monster->setId('MONSTER_'.$monsterIdCount)
            ->setName($monsterName)
            ->setSpecies($monsterSpecies)
            ->setStats($monsterStats)
            ->setRotations($this->generateRotationForMonster($monster));
            
            $monsterList[] = $monster;
        }

        return $monsterList;
    }

    /**
     * Generates Stats for a certain level and monsterType
     */
    private function generateStatsForLevel(int $level, string $monsterType, MonsterCharacter $monster)
    {
        $stats = new Stats();
        $stats->monsterCharacter = $monster;

        $stats->setLevel($level)
              ->initStatsForLevel();

        if($monsterType === MonsterCharacter::MONSTER_TYPE_DAMAGE_DEALER){
            $statTypeChoice = ['Defensif'];
            $this->weightArray($statTypeChoice, 'Offensif', 3);
        }
        else if($monsterType === MonsterCharacter::MONSTER_TYPE_TANK){
            $statTypeChoice = ['Offensif'];
            $this->weightArray($statTypeChoice, 'Defensif', 3);
        }

        // Primary Stats
        while ($stats->getPrimaryStatPoint() > 0) {
            $statTypeKey = array_rand($statTypeChoice);
            $statTypeChoosen = $statTypeChoice[$statTypeKey];

            if($statTypeChoosen === 'Offensif'){
                $statsChoice = [];
                $this->weightArray($statsChoice, 'strength', 4);
                $this->weightArray($statsChoice, 'power', 4);
            }
            else if($statTypeChoosen === 'Defensif'){
                $statsChoice = ['impassiveness'];
                $this->weightArray($statsChoice, 'stamina', 4);
                $this->weightArray($statsChoice, 'bravery', 4);
                $this->weightArray($statsChoice, 'speed', 2);
            }

            $statKey = array_rand($statsChoice);

            try {
                $stats->spendStatPoint($statsChoice[$statKey], $this->translator);
            } catch (\Throwable $th) {}
        }

        return $stats;
    }

    /**
     * Generates Rotations for a given Species and a given Level
     */
    private function generateRotationForMonster(MonsterCharacter $monsterCharacter): array
    {
        $rotation = new Rotation();

        $allAvailableAttacksForMonster = $monsterCharacter->getAvailableAttacks($this->typeRepository, $this->attackRepository);
        $actionPointLeft = $monsterCharacter->getStats()->getActionPoint();
        $cantFitAnyAttackCounter = 0;
        $currentRotationSlot = 1;

        while ($cantFitAnyAttackCounter < 10 && $currentRotationSlot < 6) {
            foreach ($allAvailableAttacksForMonster as $key => $attack) {
                if($attack->getActionPointCost() > $actionPointLeft){
                    unset($allAvailableAttacksForMonster[$key]);
                }
            }

            if($actionPointLeft === 0){
                $baseAttack = $this->attackRepository->find('ATTACK_EXPLORER_BASE');

                $rotation->setSlotAttack($currentRotationSlot, $baseAttack);
                $currentRotationSlot++;
                break;
            }

            if($monsterCharacter->monsterType === MonsterCharacter::MONSTER_TYPE_DAMAGE_DEALER){
                $actionTypeChoice = [Attack::ACTION_TYPE_SUPPORTIVE, Attack::ACTION_TYPE_SUPPORTIVE, Attack::ACTION_TYPE_DEFENSIVE];
                $this->weightArray($actionTypeChoice, Attack::ACTION_TYPE_OFFENSIVE, 6);
                $this->weightArray($actionTypeChoice, Attack::ACTION_TYPE_DEFENSIVE, 2);
            }
            else if($monsterCharacter->monsterType === MonsterCharacter::MONSTER_TYPE_TANK){
                $actionTypeChoice = [Attack::ACTION_TYPE_SUPPORTIVE, Attack::ACTION_TYPE_SUPPORTIVE, Attack::ACTION_TYPE_OFFENSIVE];
                $this->weightArray($actionTypeChoice, Attack::ACTION_TYPE_OFFENSIVE, 4);
                $this->weightArray($actionTypeChoice, Attack::ACTION_TYPE_DEFENSIVE, 3);
            }

            $actionTypeKey = array_rand($actionTypeChoice);

            $actionType = $actionTypeChoice[$actionTypeKey];
            $actionTypeAttacks = [];

            foreach ($allAvailableAttacksForMonster as $attack) {
                if($attack->getActionType() === $actionType){
                    $actionTypeAttacks[] = $attack;
                }
            }

            if(count($actionTypeAttacks) < 1){
                $cantFitAnyAttackCounter++;
                break;
            }
            
            $actionTypeAttacksKey = array_rand($actionTypeAttacks);
            $attackChoosen = $actionTypeAttacks[$actionTypeAttacksKey];
            
            $rotation->setSlotAttack($currentRotationSlot, $attackChoosen);
            
            $actionPointLeft -= $attackChoosen->getActionPointCost();
            $currentRotationSlot++;
        }
        
        $rotation->setType(Rotation::TYPE_ROTATION);

        for ($i=1; $i < 6; $i++) { 
            $attack = $rotation->getSlotAttack($i);

            if($attack === null){
                $baseAttack = $this->attackRepository->find('ATTACK_EXPLORER_BASE');

                $rotation->setSlotAttack($i, $baseAttack);
            }
        }

        $opener = new Rotation();
        $opener->setType(Rotation::TYPE_OPENER)
               ->setAttackOne($rotation->getAttackOne())
               ->setAttackTwo($rotation->getAttackTwo())
               ->setAttackThree($rotation->getAttackThree())
               ->setAttackFour($rotation->getAttackFour())
               ->setAttackFive($rotation->getAttackFive());

        return [$opener, $rotation];
    }

    private function weightArray(&$array, $value, $weight){
        for ($i=0; $i < $weight; $i++) { 
            $array[] = $value;
        }
    }

}