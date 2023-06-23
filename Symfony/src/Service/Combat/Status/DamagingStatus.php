<?php

namespace App\Service\Combat\Status;

use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\StatusInterface;

class DamagingStatus implements StatusInterface
{
    const BURN_POWER = 0.5;
    const BURN_STATUS_POWER = 0.75;
    const POISON_POWER = 1.25;
    const BAD_POISON_POWER = 1.75;

    private string $type;
    private int $casterPresence;
    private CombatLog $combatLog;
    private ?int $remainingTurns;

    public function __construct(string $type, int $casterPresence ,CombatLog &$combatLog)
    {
        $this->type = $type;
        $this->casterPresence = $casterPresence;
        $this->combatLog = $combatLog;
        $this->remainingTurns = 4;
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
    public function getPurged(Fighter $fighter)
    {
        $this->combatLog->addDamagingStatusLossLog($fighter, $this);

        $this->remainingTurns = null;
    }

    /**
     * Verifies if the status is still valid, if so reduce the remaining turn by 1.
     */
    public function manageStatus(Fighter $fighter)
    {
        if($this->remainingTurns === null){
            return ;
        }

        $this->remainingTurns -=  1;

        $fighter->receiveDamageFromStatus($this);

        if($this->remainingTurns < 1){
            $this->combatLog->addDamagingStatusLossLog($fighter, $this);

            $this->remainingTurns = null;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                          Damaging Status Functions                         */
    /* -------------------------------------------------------------------------- */

    /**
     * Returns the raw damage dealt by the status
     */
    public function getStatusDamage(Fighter $target): int
    {   
        $damagingPower = 0;

        if(ControlStatus::TYPE_DAMAGING_POISON){
            $damagingPower = self::POISON_POWER;
        }
        else if(ControlStatus::TYPE_DAMAGING_BAD_POISON){
            $damagingPower = self::BAD_POISON_POWER;
        } 
        else if (ControlStatus::TYPE_DAMAGING_BURN){
            $damagingPower = self::BURN_POWER;
        }

        return Fighter::minValueOfOne(Fighter::calculateDamagingStatusValue($damagingPower, $this->casterPresence, $target->getImpassiveness(), $target->getLevel()));
    }

    /**
     * Returns the raw value of the effect done by the status
     */
    public function getStatusEffectValue(Fighter $target): ?int
    {
        $statusPower = 0;

        if(ControlStatus::TYPE_DAMAGING_BURN){
            $statusPower = self::BURN_STATUS_POWER;
        }

        return Fighter::minValueOfOne(Fighter::calculateDamagingStatusValue($statusPower, $this->casterPresence, $target->getImpassiveness(), $target->getLevel()));
    }
}