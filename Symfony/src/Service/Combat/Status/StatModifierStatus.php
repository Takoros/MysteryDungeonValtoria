<?php

namespace App\Service\Combat\Status;

use App\Service\Combat\Status\StatusInterface;

class StatModifierStatus implements StatusInterface
{
    private int $characterFighterId;
    private ?int $remainingTurns;
    private int $value;
    
    // "agility", "strength" etc..
    private string $statToModify;

    private string $type;

    public function __construct(int $characterFighterId, int $remainingTurns, string $statToModify,
                                int|float $value, string $type)
    {
        $this->characterFighterId = $characterFighterId;
        $this->remainingTurns = $remainingTurns;
        $this->statToModify = $statToModify;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Returns the type of the status
     */
    public function getStatusType(): string
    {
        return $this->type;        
    }

    /**
     * Returns the id of the character that suffers the status
     */
    public function getCharacterFighterId(): int
    {
        return $this->characterFighterId;
    }

    /**
     * Returns the remaining number of turns of the status
     */
    public function getRemainingTurns(): ?int
    {
        return $this->remainingTurns;
    }

    /**
     * Returns the stat that the status modifies
     */
    public function getStatToModify(): string
    {
        return $this->statToModify;
    }

    /**
     * Returns the value of the modifier (Ex: 8 or -4)
     */
    public function getValue(): int|float
    {
        return $this->value;
    }

    /**
     * Verifies if the status is still valid, if so reduce remaining turn by 1.
     */
    public function useTurn(): void
    {
        if($this->remainingTurns === null){
            return ;
        }

        $this->remainingTurns = $this->remainingTurns - 1;

        if($this->remainingTurns < 1){
            $this->remainingTurns = null;
            $this->value = 0;
        }
    }
}