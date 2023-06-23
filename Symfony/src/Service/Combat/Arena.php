<?php

namespace App\Service\Combat;

use App\Entity\CombatLog;
use App\Repository\AttackRepository;
use DateTime;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Primary class used for combats, manage all the fight.
 */
class Arena
{
    /** Type of combat played in the arena **/
    const TYPE_PVP = 'PVP';
    const TYPE_PVE = 'PVE';
    const TYPE_EVE = 'EVE';

    /** Victories **/
    const TEAM_ONE_WIN = 1;
    const TEAM_TWO_WIN = 2;
    const DRAW = 0;

    /** Maximum of rounds playable before the combat stop to prevent infinite fights */
    const MAX_ROUND = 100;

    /**
     * Character / Fighters Properties
     */
    public array $teamOneCharacters;
    public array $teamTwoCharacters;
    public array $teamOneFighters = [];
    public array $teamTwoFighters = [];

    /**
     * Combat Info Properties
     */
    public CombatLog $combatLog;
    public array $turnOrder = [];
    public int $currentRound = 1;
    public bool $isEnded = false;
    public ?int $winner = null;
    public ?string $type = null;

    /**
     * Access Properties
     */
    public AttackRepository $attackRepository;

    public function __construct(array $teamOneCharacters, array $teamTwoCharacters, string $type, AttackRepository $attackRepository)
    {
        $this->teamOneCharacters = $teamOneCharacters;
        $this->teamTwoCharacters = $teamTwoCharacters;
        $this->combatLog = new CombatLog();
        $this->combatLog->arena = $this;
        $this->type = $type;
        $this->attackRepository = $attackRepository;
        

        $this->prepareFighters();
    }

    /**
     * Prepares all the Fighters necessary for the fight
     */
    private function prepareFighters(): void
    {
        if($this->type === self::TYPE_PVP){
            foreach ($this->teamOneCharacters as $character) {
                $fighter = new Fighter($character, 1, $this, $this->combatLog);

                $this->teamOneFighters[] = $fighter;
            }

            foreach ($this->teamTwoCharacters as $character) {
                $fighter = new Fighter($character, 2, $this, $this->combatLog);

                $this->teamTwoFighters[] = $fighter;
            }
        }
    }

    /**
     * Initiate the battle, and play it until one of the team wins
     */
    public function launchBattle(): void
    {
        $this->prepareTurnOrder();

        while (!$this->isEnded) {
            $this->prepareTurnOrder();

            foreach ($this->turnOrder as $currentTurn) {
                if(!$this->isEnded && !$currentTurn['fighter']->isKo()){
                    if($currentTurn['fighter']->isPrepared()){
                        $enemyTeam = $this->getEnemyTeam($currentTurn['fighter']->getTeam(), true);
                        $allyTeam = $this->getAllyTeam($currentTurn['fighter']->getTeam(), true);

                        $currentTurn['fighter']->playTurn($enemyTeam, $allyTeam);

                        $this->checkBattleState();
                        $this->currentRound++;
                    }
                    else {
                        $currentTurn['fighter']->prepare();
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

        foreach ($this->teamOneFighters as $fighter) {
            if (!$fighter->isKo()){
                $isTeamOneKO = false;
            }
        }

        foreach ($this->teamTwoFighters as $fighter) {
            if (!$fighter->isKo()){
                $isTeamTwoKO = false;
            }
        }

        if($isTeamOneKO && $isTeamTwoKO){
            $this->winner = self::DRAW;
            $this->isEnded = true;
            $this->combatLog->addWinnerLog(self::DRAW);

            return ;
        }
        else if($isTeamOneKO){
            $this->winner = self::TEAM_TWO_WIN;
            $this->isEnded = true;
            $this->combatLog->addWinnerLog(self::TEAM_TWO_WIN);

            return ;
        }
        else if($isTeamTwoKO){
            $this->winner = self::TEAM_ONE_WIN;
            $this->isEnded = true;
            $this->combatLog->addWinnerLog(self::TEAM_ONE_WIN);

            return ;
        }
    }

    /**
     * Prepares the turn order array, in order to know which character will play first
     */
    private function prepareTurnOrder(): void 
    {
        $turnOrder = [];

        foreach (array_merge($this->teamOneFighters, $this->teamTwoFighters) as $key => &$fighter) {
            if(!$fighter->isKo()){
                $turnOrder[] = [
                    'key' => $key,
                    'team' => $fighter->getTeam(),
                    'fighterId' => $fighter->getId(),
                    'fighterSpeed' => $fighter->getBaseSpeed(),
                    'fighter' => &$fighter
                ];    
            }
        }

        usort($turnOrder, function($a, $b){
            return $b['fighterSpeed'] <=> $a['fighterSpeed'];
        });

        $this->turnOrder = $turnOrder;
    }

    /**
     * Returns the enemy team array of the team number given
     */
    private function getEnemyTeam(int $teamNumber, bool $onlyAliveMembers = false): array
    {
        if($teamNumber === 1){
            $enemyTeam = $this->teamTwoFighters;
        }
        else if ($teamNumber === 2){
            $enemyTeam = $this->teamOneFighters;
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
    private function getAllyTeam(int $teamNumber, bool $onlyAliveMembers = false): array
    {
        if($teamNumber === 1){
            $allyTeam = $this->teamOneFighters;
        }
        else if ($teamNumber === 2){
            $allyTeam = $this->teamTwoFighters;
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