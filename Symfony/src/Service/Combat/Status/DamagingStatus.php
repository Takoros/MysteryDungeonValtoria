<?php

namespace App\Service\Combat\Status;

use App\Entity\CombatLog;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\StatusInterface;

class DamagingStatus implements StatusInterface
{
    const DAMAGING_BURN = 'burn';
    const DAMAGING_BURN_POWER = 0.5;
    const DAMAGING_BURN_STATUS_POWER = 0.75;

    const DAMAGING_POISON = 'poison';
    const DAMAGING_POISON_POWER = 1.25;

    const DAMAGING_BAD_POISON = 'bad poison';
    const DAMAGING_BAD_POISON_POWER = 1.75;

    private ?int $remainingTurns;
    private string $damagingType;
    private int $casterPresence;
    private string $type;
    private CombatLog $combatLog;

    public function __construct(string $damagingType, int $casterPresence ,CombatLog &$combatLog)
    {
        $this->damagingType = $damagingType;
        $this->type = StatusInterface::TYPE_DAMAGING;
        $this->casterPresence = $casterPresence;
        $this->combatLog = $combatLog;

        switch ($damagingType) {
            case self::DAMAGING_BURN:
                $this->remainingTurns = 4;
                break;
            case self::DAMAGING_POISON:
                $this->remainingTurns = 4;
                break;
            case self::DAMAGING_BAD_POISON:
                $this->remainingTurns = 4;
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
     * Returns the caster presence
     */
    public function getCasterPresence(): int
    {
        return $this->casterPresence;
    }

    /**
     * Returns the type of damaging status
     */
    public function getDamagingType(): string
    {
        return $this->damagingType;
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
        $this->combatLog->addDamagingStatusLossLog($this, $fighter);

        $this->remainingTurns = null;
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

        $fighter->receiveDamageFromStatus($this);

        if($this->remainingTurns < 1){
            $this->combatLog->addDamagingStatusLossLog($this, $fighter);

            $this->remainingTurns = null;
        }
    }
}