<?php

namespace App\Service\Combat;

use App\Entity\CombatLog;
use App\Repository\AttackRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class Arena
{
    const TYPE_PVP = 'PVP';
    const TYPE_PVE = 'PVE';
    const TYPE_EVE = 'EVE';
    const TEAM_ONE_WIN = 1;
    const TEAM_TWO_WIN = 2;
    const DRAW = 0;

    const MAX_ROUND = 100;

    public array $teamOneCharacters;
    public array $teamTwoCharacters;
    public array $teamOneCharacterFighters = [];
    public array $teamTwoCharacterFighters = [];
    public CombatLog $combatLog;
    public array $combatLogContent = [];
    public array $turnOrder = [];

    public int $currentRound = 1;
    public bool $isEnded = false;
    public ?int $winner = null;
    public ?string $type = null;

    private $attackRepository;

    public function __construct(array $teamOneCharacters, array $teamTwoCharacters, string $type, AttackRepository $attackRepository)
    {
        $this->teamOneCharacters = $teamOneCharacters;
        $this->teamTwoCharacters = $teamTwoCharacters;
        $this->type = $type;
        $this->combatLog = new CombatLog();
        $this->attackRepository = $attackRepository;

        $this->prepareCharacterFighters();
    }

    /**
     * Prepare the characterFighters for both teams.
     */
    private function prepareCharacterFighters(): void
    {
        if(!$this->type === self::TYPE_EVE){
            foreach ($this->teamOneCharacters as $character) {
                $characterFighter = new CharacterFighter(
                    $character->getId(),
                    $character->getName(),
                    $character->getGender(),
                    $character->getLevel(),
                    $character->getSpecies(),
                    $character->getTypes(),
                    1,
                    $this->attackRepository
                );

                $characterFighter->initiateCombatProperties($character->getOpenerRotation(), $character->getRotation(), $character->getStats());
    
                $this->teamOneCharacterFighters[] = $characterFighter;
            }
        }
        else {
            // TODO : Monster Character Fighter
        }

        if($this->type === self::TYPE_PVP){
            foreach ($this->teamOneCharacters as $character) {
                $characterFighter = new CharacterFighter(
                    $character->getId(),
                    $character->getName(),
                    $character->getGender(),
                    $character->getLevel(),
                    $character->getSpecies(),
                    $character->getTypes(),
                    1,
                    $this->attackRepository
                );

                $characterFighter->initiateCombatProperties($character->getOpenerRotation(), $character->getRotation(), $character->getStats());
    
                $this->teamOneCharacterFighters[] = $characterFighter;
            }

            foreach ($this->teamTwoCharacters as $character) {
                $characterFighter = new CharacterFighter(
                    $character->getId(),
                    $character->getName(),
                    $character->getGender(),
                    $character->getLevel(),
                    $character->getSpecies(),
                    $character->getTypes(),
                    2,
                    $this->attackRepository
                );

                $characterFighter->initiateCombatProperties($character->getOpenerRotation(), $character->getRotation(), $character->getStats());
    
                $this->teamTwoCharacterFighters[] = $characterFighter;
            }
        }
        else {
            // TODO : Monster Character Fighter
        }
    }

    /**
     * Initiate the battle, and play it until one of the team wins.
     */
    public function launchBattle(): void
    {
        $this->combatLog->setTeamOne($this->teamOneCharacters);
        $this->combatLog->setTeamTwo($this->teamTwoCharacters);
        $this->combatLogContent[] = 'Début du combat';

        while (!$this->isEnded) {
            $this->prepareTurnOrder();

            foreach ($this->turnOrder as $currentTurn) {
                if(!$this->isEnded && !$currentTurn['characterFighter']->isKo()){
                    if($currentTurn['characterFighter']->isPrepared()){
                        $enemyTeam = $this->getEnemyTeam($currentTurn['characterFighter']->getTeam(), true);
                        $allyTeam = $this->getAllyTeam($currentTurn['characterFighter']->getTeam(), true);
                        $turnLogs = $currentTurn['characterFighter']->playTurn($enemyTeam, $allyTeam);
                        
                        foreach ($turnLogs as $log) {
                            $this->combatLogContent[] = $log;
                        }

                        $this->checkBattleState();
                        $this->currentRound++;
                    }
                    else {
                        $currentTurn['characterFighter']->prepare();
                    }
                }
            }
        }
    }

    /**
     * Verifies if the battle is ended.
     */
    private function checkBattleState(): void 
    {
        if($this->currentRound >= self::MAX_ROUND){
            $this->winner = self::DRAW;
            $this->isEnded = true;

            return ; 
        }

        $isTeamOneKO = true;
        $isTeamTwoKO = true;

        foreach ($this->teamOneCharacterFighters as $characterFighter) {
            if (!$characterFighter->isKo()){
                $isTeamOneKO = false;
            }
        }

        foreach ($this->teamTwoCharacterFighters as $characterFighter) {
            if (!$characterFighter->isKo()){
                $isTeamTwoKO = false;
            }
        }

        if($isTeamOneKO && $isTeamTwoKO){
            $this->winner = self::DRAW;
            $this->isEnded = true;
            $this->combatLogContent[] = "Égalité";

            return ;
        }
        else if($isTeamOneKO){
            $this->winner = self::TEAM_TWO_WIN;
            $this->isEnded = true;
            $this->combatLogContent[] = "Victoire de l'équipe 2";

            return ;
        }
        else if($isTeamTwoKO){
            $this->winner = self::TEAM_ONE_WIN;
            $this->isEnded = true;
            $this->combatLogContent[] = "Victoire de l'équipe 1";

            return ;
        }
    }

    /**
     * Prepares the turn order array, in order to know which character will play first
     */
    private function prepareTurnOrder(): void 
    {
        $turnOrder = [];

        foreach (array_merge($this->teamOneCharacterFighters, $this->teamTwoCharacterFighters) as $key => &$characterFighter) {
            if(!$characterFighter->isKo()){
                $turnOrder[] = [
                    'key' => $key,
                    'team' => $characterFighter->getTeam(),
                    'characterFighterId' => $characterFighter->getId(),
                    'characterFighterSpeed' => $characterFighter->getTotalSpeed(),
                    'characterFighter' => &$characterFighter
                ];    
            }
        }

        usort($turnOrder, function($a, $b){
            return $a['characterFighterSpeed'] <=> $b['characterFighterSpeed'];
        });

        $this->turnOrder = $turnOrder;
    }

    /**
     * Returns the enemy team array of the team number given
     */
    private function getEnemyTeam($teamNumber, $onlyAliveMembers = false): array
    {
        if($teamNumber === 1){
            $enemyTeam = $this->teamTwoCharacterFighters;
        }
        else if ($teamNumber === 2){
            $enemyTeam = $this->teamOneCharacterFighters;
        }
        else {
            throw new InvalidParameterException();
        }
                    
        if($onlyAliveMembers){
            $enemyTeamTmp = [];

            foreach ($enemyTeam as $enemy) {
                if(!$enemy->isKo()){
                    $enemyTeamTmp[] = $enemy;
                }
            }

            $enemyTeam = $enemyTeamTmp;
        }

        return $enemyTeam;
    }
    
     /**
     * Returns the ally team array of the team number given
     */
    private function getAllyTeam($teamNumber, $onlyAliveMembers = false): array
    {
        if($teamNumber === 1){
            $allyTeam = $this->teamOneCharacterFighters;
        }
        else if ($teamNumber === 2){
            $allyTeam = $this->teamTwoCharacterFighters;
        }
        else {
            throw new InvalidParameterException();
        }
                    
        if($onlyAliveMembers){
            $allyTeamTmp = [];

            foreach ($allyTeam as $ally) {
                if(!$ally->isKo()){
                    $allyTeamTmp[] = $ally;
                }
            }

            $allyTeam = $allyTeamTmp;
        }

        return $allyTeam;
    }
}