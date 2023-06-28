<?php

namespace App\Service\Dungeon;

use App\Entity\Dungeon;
use App\Entity\DungeonInstance;
use stdClass;

class DungeonGenerationService
{
    const DUNGEON_SIZE_SMALL = 'small';
    const DUNGEON_SIZE_MEDIUM = 'medium';
    const DUNGEON_SIZE_BIG = 'big';

    const DUNGEON_BOX_FULL = 'box-full';
    const DUNGEON_BOX_ENTRANCE = 'box-entrance';
    const DUNGEON_BOX_EXIT = 'box-exit';
    const DUNGEON_BOX_EMPTY = 'box-empty';

    const DUNGEON_TILE_EXPLORATED = 'tile-explorated';
    const DUNGEON_TILE_UNKNOWN = 'tile-unknow';

    private const UP = DungeonInstance::MOVE_DIRECTION_UP;
    private const DOWN = DungeonInstance::MOVE_DIRECTION_DOWN;
    private const LEFT = DungeonInstance::MOVE_DIRECTION_LEFT;
    private const RIGHT = DungeonInstance::MOVE_DIRECTION_RIGHT;

    /**
     * Procedurally generates a new Dungeon
     */
    public function generateDungeon(Dungeon $dungeon, string $size)
    {
        $sizeValues = $this->getSizeValues($size);
        $emptyDungeonMap = $this->createEmptyDungeonMap($sizeValues->dimensions);
        $currentPosition = $startingPoint = $this->defineStartingPoint($emptyDungeonMap['data']);
        $emptyDungeonMapContent = $emptyDungeonMap['content'];

        $lastDirection = null;
        $currentDirection = $this->getRandomDirection();

        $maxTunnels = $sizeValues->maxTunnels;
        $numberOfRooms = 0;

        while($maxTunnels > 0){

            $tunnelLength = 0;
            $randomLength = rand(1, $sizeValues->maxLength);

            if($lastDirection === null){
                $currentDirection = $this->getRandomDirection();
            }
            else {
                $currentDirection = $this->getTurnDirection($lastDirection);
            }
            
            while($tunnelLength < $randomLength){
                if($this->willHitEdgeOfMap($currentDirection, $currentPosition, $emptyDungeonMapContent)){
                    break;
                }
                else {
                    $emptyDungeonMapContent[$currentPosition]['box'] = self::DUNGEON_BOX_FULL;
                    $currentPosition = $this->move($currentDirection, $currentPosition);
                    $tunnelLength++;
                    $numberOfRooms++;
                }
            }
            
            if($tunnelLength > 0){
                $lastDirection = $currentDirection;
                $maxTunnels--;
            }
            
            if($numberOfRooms < 11 && $maxTunnels === 0){
                $maxTunnels++;
            }
        }

        $emptyDungeonMapContent[$startingPoint]['box'] = self::DUNGEON_BOX_ENTRANCE;
        $emptyDungeonMapContent[$startingPoint]['isExplorated'] = self::DUNGEON_TILE_EXPLORATED;
        $emptyDungeonMapContent[$currentPosition]['box'] = self::DUNGEON_BOX_EXIT;
        
        $this->placeMonstersInDungeon($dungeon, $emptyDungeonMapContent);

        return [
            'content' => [
                'dungeon' => (array) $emptyDungeonMapContent,
                'data' => $emptyDungeonMap['data'],
            ],
            'currentExplorersPosition' => $startingPoint
        ];
    }

    private function willHitEdgeOfMap($currentDirection, $currentPosition, $mapContent){
        $newPosition = $this->move($currentDirection, $currentPosition);
        
        if(array_key_exists($newPosition, $mapContent)){
            return false;
        }

        return true;
    }

    public function willHitEdgeWallsOfMap($direction, $currentPosition, $mapContent){
        $newPosition = $this->move($direction, $currentPosition);

        if(!array_key_exists($newPosition, $mapContent) || $mapContent[$newPosition]['box'] === self::DUNGEON_BOX_EMPTY){
            return true;
        }

        return false;
    }

    public function move($direction, $position)
    {
        $explodedPosition = explode(',', $position);
        $currentY = (int) $explodedPosition[0];
        $currentX = (int) $explodedPosition[1];

        switch ($direction) {
            case self::UP:
                $currentY++;
                return $currentY . ',' . $currentX;
                break;
            case self::DOWN:
                $currentY--;
                return $currentY . ',' . $currentX;
                break;
            case self::LEFT:
                $currentX--;
                return $currentY . ',' . $currentX;
                break;
            case self::RIGHT:
                $currentX++;
                return $currentY . ',' . $currentX;
                break;
        }
    }

    private function getTurnDirection(string $currentDirection): string
    {
        $nextDirection = '';

        switch ($currentDirection) {
            case self::UP:
            case self::DOWN:
                $directionArray = [self::LEFT, self::RIGHT];
                $chosenIndex = array_rand($directionArray);

                $nextDirection = $directionArray[$chosenIndex];
                break;
            case self::LEFT:
            case self::RIGHT:
                $directionArray = [self::UP, self::DOWN];

                $chosenIndex = array_rand($directionArray);

                $nextDirection = $directionArray[$chosenIndex];
                break;
        }

        return $nextDirection;
    }

    private function getRandomDirection(): string
    {
        $directionArray = [self::UP, self::DOWN, self::RIGHT, self::LEFT];
        $indexChosen = array_rand($directionArray);

        return $directionArray[$indexChosen];
    }

    /**
     * Returns the size values depending on what size is asked
     */
    private function getSizeValues(string $size): stdClass
    {
        $sizeValues = new stdClass();

        switch ($size) {
            case self::DUNGEON_SIZE_SMALL:
                $sizeValues->dimensions = rand(8,10);
                $sizeValues->maxTunnels = rand(4,5);
                $sizeValues->maxLength = rand(6,7);
                break;
            case self::DUNGEON_SIZE_MEDIUM:
                $sizeValues->dimensions = rand(12,14);
                $sizeValues->maxTunnels = rand(7,9);
                $sizeValues->maxLength = rand(7,8);
                break;
            case self::DUNGEON_SIZE_BIG:
                $sizeValues->dimensions = rand(16,18);
                $sizeValues->maxTunnels = rand(11,13);
                $sizeValues->maxLength = rand(9,11);
                break;
        }

        return $sizeValues;
    }

    /**
     * Returns an empty dungeon map (multidimensional array)
     */
    private function createEmptyDungeonMap($dimension){
        if($dimension % 2 == 0){
            $xEndingPoint = $dimension / 2;
            $xstartingPoint = gmp_intval(gmp_neg($xEndingPoint));

            $yEndingPoint = $dimension / 2;
            $ystartingPoint = gmp_intval(gmp_neg($yEndingPoint));
        }
        else {
            $xEndingPoint = ceil($dimension / 2); // 2
            $yEndingPoint = floor($dimension / 2); // 1
            $xstartingPoint = gmp_intval(gmp_neg($yEndingPoint)); 
            $ystartingPoint = gmp_intval(gmp_neg($xEndingPoint)); 
        }
        
        $emptyDungeonMap = [];

        for ($x = $xstartingPoint; $x < $xEndingPoint; $x++) { 
            for ($y = $ystartingPoint; $y < $yEndingPoint; $y++) {
                $coordinates = strval($y).','.strval($x);
                $emptyDungeonMap[$coordinates] = [];
                $emptyDungeonMap[$coordinates]['box'] = self::DUNGEON_BOX_EMPTY;
                $emptyDungeonMap[$coordinates]['isExplorated'] = self::DUNGEON_TILE_UNKNOWN;
            }
        }

        return [
            'content' => (array) $emptyDungeonMap,
            'data' => [
                'minX' => $xstartingPoint,
                'maxX' => $xEndingPoint-1,
                'minY' => $ystartingPoint,
                'maxY' => $yEndingPoint-1,
            ]
        ];
    }

    /**
     * returns coordinates of the starting point
     */
    private function defineStartingPoint(array $dungeonMapData): string
    {
        $currentX = rand($dungeonMapData['minX'], $dungeonMapData['maxX']);
        $currentY = rand($dungeonMapData['minY'], $dungeonMapData['maxY']);

        return $currentY.",".$currentX;
    }

    /**
     * Place some monsters in the dungeon
     */
    private function placeMonstersInDungeon(Dungeon $dungeon, &$dungeonMap): array
    {
        $monstersLivingList = $dungeon->getMonsterLivingList();
        $noMonsterCounter = 0;
        
        foreach ($dungeonMap as $key => $tile) {
            if($tile['box'] === self::DUNGEON_BOX_FULL){
                $chance = (25 + ($noMonsterCounter * 3)) * 1.25;

                $diceRoll = rand(1, 100);

                if($diceRoll < $chance){
                    $chances = [1,1,1,1,1,1,1,1,2,2,2,2,2,3,3];
                    $indexChoosen = array_rand($chances);

                    $dungeonMap[$key]['monsters'] = $this->chooseMonstersInList($monstersLivingList, $chances[$indexChoosen]);
                    $noMonsterCounter = 0;
                }
                else {
                    $noMonsterCounter++;
                }
            }
        }

        return $dungeonMap;
    }

    private function chooseMonstersInList(array $monsterLivingList, int $numberOfMonsters): array
    {
        $choices = [];

        foreach ($monsterLivingList as $monster) {
            for ($i=0; $i < $monster['rate']; $i++) { 
                array_push($choices, $monster['species']);
            }
        }
        
        $indexChoosen = array_rand($choices, $numberOfMonsters);

        $monsters = [];

        if(gettype($indexChoosen) === gettype([])) {
            foreach ($indexChoosen as $key) {
                $monsters[] = $choices[$key];
            } 
        }
        else {
            $monsters[] = $choices[$indexChoosen];
        }

        return $monsters;
    }
}