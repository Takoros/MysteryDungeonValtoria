<?php

namespace App\Service\Combat\Status;

use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\StatusInterface;

class StatisticModifierStatus implements StatusInterface
{
    private ?int $remainingTurns;
    private int $modifier;
    private string $statisticModified;
    private string $type;
    private CombatLog $combatLog;

    public function __construct(int $remainingTurns, string $statisticModified, int $modifier, string $type, CombatLog &$combatLog)
    {
        $this->remainingTurns = $remainingTurns;
        $this->statisticModified = $statisticModified;
        $this->modifier = $modifier;
        $this->type = $type;
        $this->combatLog = $combatLog;
    }

    /**
     * Returns the remaining number of turns of the status
     */
    public function getRemainingTurns(): ?int
    {
        return $this->remainingTurns;
    }

    /**
     * Returns the value of the modifier
     */
    public function getModifier(): int
    {
        return $this->modifier;
    }

    /**
     * Returns the stat that the status modifies
     */
    public function getStatisticModified(): string
    {
        return $this->statisticModified;
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
        $this->combatLog->addStatisticModifierStatusLossLog($fighter, $this);

        $this->remainingTurns = null;
        $this->modifier = 0;
    }

    /**
     * Sets the new modifier after reduction applied by target's impassiveness
     * 
     * Only for nerfs
     */
    public function setModifierAfterReduction(int $modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Verifies if the status is still valid, if so reduce remaining turn by 1.
     */
    public function manageStatus(Fighter $fighter): void
    {
        if($this->remainingTurns === null){
            return ;
        }

        $this->remainingTurns -=  1;

        if($this->remainingTurns < 1){
            $this->combatLog->addStatisticModifierStatusLossLog($fighter, $this);

            $this->remainingTurns = null;
            $this->modifier = 0;

            if($this->statisticModified === 'vitality' && $fighter->getCurrentVitality() > $fighter->getVitality()){
                $fighter->setCurrentVitality($fighter->getVitality());
            }
        }
    }
}