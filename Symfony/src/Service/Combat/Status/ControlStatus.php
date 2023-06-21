<?php

namespace App\Service\Combat\Status;

use App\Entity\CombatLog;
use App\Service\Combat\Status\StatusInterface;

class ControlStatus implements StatusInterface
{
    const CONTROL_PETRIFICATION = 'petrification';
    const CONTROL_PARALYSIS = 'paralysis';
    const CONTROL_CONFUSION = 'confusion';
    const CONTROL_FATIGUE = 'fatigue';
    const CONTROL_FREEZE = 'freeze';
    const CONTROL_SLEEP = 'sleep';
    const CONTROL_YAWN = 'yawn';

    const CONTROL_PARALYSIS_CHANCE = 25;
    const CONTROL_CONFUSION_CHANCE = 33;
    const CONTROL_FREEZE_CHANCE = 50;

    private ?int $remainingTurns;
    private string $controlType;
    private string $type;
    private CombatLog $combatLog;

    public function __construct(string $controlType, CombatLog &$combatLog)
    {
        $this->controlType = $controlType;
        $this->type = StatusInterface::TYPE_CONTROL;
        $this->combatLog = $combatLog;

        switch ($controlType) {
            case self::CONTROL_PARALYSIS:
                $this->remainingTurns = 5;
                break;
            case self::CONTROL_FREEZE:
                $this->remainingTurns = 2;
                break;
            case self::CONTROL_SLEEP:
                $this->remainingTurns = rand(2,3);
                break;
            case self::CONTROL_CONFUSION:
                $this->remainingTurns = 3;
                break;
            case self::CONTROL_PETRIFICATION:
                $this->remainingTurns = 1;
                break;
            case self::CONTROL_FATIGUE:
                $this->remainingTurns = 2;
                break;
            case self::CONTROL_YAWN:
                $this->remainingTurns = 1;
                break;
        }
    }

    /**
     * Returns the remaining number of turns of the status
     */
    public function getRemainingTurns(): ?int
    {
        return $this->remainingTurns;
    }

    /**
     * Returns the type of control
     */
    public function getControlType(): string
    {
        return $this->controlType;
    }

    /**
     * Returns the type of the status
     */
    public function getStatusType(): string
    {
        return $this->type;        
    }

    /**
     * Purge the status
     */
    public function getPurged($fighter): void
    {
        $this->combatLog->addControlStatusLossLog($fighter, $this);

        $this->remainingTurns = null;
    }

    /**
     * Verifies if the status is still valid, if so reduce remaining turn by 1.
     */
    public function manageStatus($fighter): void
    {
        if($this->remainingTurns === null){
            return ;
        }

        $this->remainingTurns -=  1;

        if($this->controlType === self::CONTROL_FREEZE && $this->doFreezeDeactivate()){
            $this->combatLog->addStringLog('La glace autour de {$fighter->getName()} à fondu.');
            $this->combatLog->addControlStatusLossLog($fighter, $this);

            $this->remainingTurns = null;

            return;
        }

        if($this->remainingTurns < 1){
            $this->combatLog->addControlStatusLossLog($fighter, $this);

            $this->remainingTurns = null;
        }
    }

    /**
     * Purge the 
     */

    /* -------------------------------------------------------------------------- */
    /*                              Static functions                              */
    /* -------------------------------------------------------------------------- */

    /**
     * Returns true if a paralysis prevent a fighter from attacking
     */
    public static function doParalysisActivate(): bool
    {
        $diceRoll = rand(1, 100);

        if($diceRoll <= self::CONTROL_PARALYSIS_CHANCE){
            return true;
        }

        return false;
    }

    /**
     * Returns true if a confusion makes a fighter hit itself
     */
    public static function doConfusionActivate(): bool
    {
        $diceRoll = rand(1, 100);

        if($diceRoll <= self::CONTROL_CONFUSION_CHANCE){
            return true;
        }

        return false;
    }

    /**
     * Returns true if the freeze status expire due to luck
     */
    private function doFreezeDeactivate(): bool
    {
        $diceRoll = rand(1, 100);

        if($diceRoll <= self::CONTROL_FREEZE_CHANCE){
            return true;
        }

        return false;
    }
}