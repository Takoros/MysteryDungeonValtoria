<?php

namespace App\Service\Combat\Status;

use App\Entity\CombatLog;
use App\Service\Combat\Status\StatusInterface;

class ControlStatus implements StatusInterface
{
    const PARALYSIS_CHANCE = 25;
    const CONFUSION_CHANCE = 33;
    const FREEZE_CHANCE = 50;

    private string $type;
    private CombatLog $combatLog;
    private ?int $remainingTurns;

    public function __construct(string $type, CombatLog &$combatLog)
    {
        $this->type = $type;
        $this->combatLog = $combatLog;

        switch ($type) {
            case StatusInterface::TYPE_CONTROL_PARALYSIS:
                $this->remainingTurns = 5;
                break;
            case StatusInterface::TYPE_CONTROL_FREEZE:
                $this->remainingTurns = 2;
                break;
            case StatusInterface::TYPE_CONTROL_SLEEP:
                $this->remainingTurns = rand(2,3);
                break;
            case StatusInterface::TYPE_CONTROL_CONFUSION:
                $this->remainingTurns = 3;
                break;
            case StatusInterface::TYPE_CONTROL_PETRIFICATION:
                $this->remainingTurns = 1;
                break;
            case StatusInterface::TYPE_CONTROL_FATIGUE:
                $this->remainingTurns = 2;
                break;
            case StatusInterface::TYPE_CONTROL_YAWN:
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

        if($this->remainingTurns < 1 || ($this->type === StatusInterface::TYPE_CONTROL_FREEZE && $this->doFreezeDeactivate())){
            $this->combatLog->addControlStatusLossLog($fighter, $this);

            $this->remainingTurns = null;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                          Control Status Functions                          */
    /* -------------------------------------------------------------------------- */

    /**
     * Returns true if a paralysis prevent a fighter from attacking
     */
    public static function doParalysisActivate(): bool
    {
        $diceRoll = rand(1, 100);

        if($diceRoll <= self::PARALYSIS_CHANCE){
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

        if($diceRoll <= self::CONFUSION_CHANCE){
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

        if($diceRoll <= self::FREEZE_CHANCE){
            return true;
        }

        return false;
    }
}
