<?php

namespace App\Service\Combat;

use App\Entity\Attack;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use Exception;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class used as lines of CombatLogs
 */
class LogLine
{
    const TYPE_KO = 'type_ko';
    const TYPE_INFO = 'type_info';
    const TYPE_DODGE = 'type_dodge';
    const TYPE_ATTACK = 'type_attack';
    const TYPE_WINNER = 'type_winner';
    const TYPE_ATTACK_NO_EFFECT = 'type_attack_no_effect';
    const TYPE_REMAINING_VITALITY = 'type_remaining_vitality';
    const TYPE_CONTROL_STATUS_LOSS = 'type_control_status_loss';
    const TYPE_DAMAGING_STATUS_LOSS = 'type_damaging_status_loss';
    const TYPE_HEALING_TAKEN_BY_ATTACK = 'type_healing_taken_by_attack';
    const TYPE_DAMAGE_TAKEN_BY_ATTACK = 'type_damage_taken_by_attack';
    const TYPE_DAMAGE_TAKEN_BY_STATUS = 'type_damage_taken_by_status';
    const TYPE_CONTROL_STATUS_RECEIVED = 'type_control_status_received';
    const TYPE_CONTROL_STATUS_ACTIVATE = 'type_control_status_activate';
    const TYPE_STATISTIC_MODIFIER_LOSS = 'type_statistic_modifier_loss';
    const TYPE_CONTROL_STATUS_NO_EFFECT = 'type_control_status_no_effect';
    const TYPE_DAMAGING_STATUS_RECEIVED = 'type_damaging_status_received';
    const TYPE_STATISTIC_MODIFIER_RECEIVED = 'type_statistic_modifier_received';

    private string $type;
    private object $data;
    private int $roundNumber;

    /**
     * Only use $type and $roundNumber
     */
    public function __construct(string $type = 'error', int $roundNumber = 0, array $data = []) {
        $this->type = $type;
        $this->roundNumber = $roundNumber;
        $this->data = (object) $data;

        if($this->type === 'error' || $this->roundNumber === 0){
            throw new InvalidParameterException('LogLine needs a type|roundNumber parameter.');
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function getData(): object
    {
        return $this->data;
    }

    /**
     * Returns true if the type of the logline is the same as the one passed in the parameter.
     */
    private function isType($type): bool
    {
        if($this->type === $type){
            return true;
        }

        return false;
    }

    /**
     * Initiates a Winner LogLine
     */
    public function initTypeWinner(int $winner): self
    {
        if(!$this->isType(self::TYPE_WINNER)){
            throw new Exception('Wrong type of LogLine used.');
        }
        
        $this->data = (object) [
            'winner' => $winner
        ];

        return $this;
    }

    /**
     * Initiates a Attack LogLine
     */
    public function initTypeAttack(Fighter $fighter, Attack $attack, bool $isCrit): self
    {
        if(!$this->isType(self::TYPE_ATTACK)){
            throw new Exception('Wrong type of LogLine used.');
        }
        
        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'attackName' => $attack->getName(),
            'isCrit' => $isCrit,
        ];

        return $this;
    }

    /**
     * Initiates a Dodge LogLine
     */
    public function initTypeDodge(Fighter $fighter): self
    {
        if(!$this->isType(self::TYPE_DODGE)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName()
        ];

        return $this;
    }

    /**
     * Initiates a DamageTakenByAttack LogLine
     */
    public function initTypeDamageTakenByAttack(Fighter $fighter, int $physicalDamageTaken, int $specialDamageTaken): self
    {
        if(!$this->isType(self::TYPE_DAMAGE_TAKEN_BY_ATTACK)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'physicalDamage' => $physicalDamageTaken,
            'specialDamage' => $specialDamageTaken
        ];

        return $this;
    }

    /**
     * Initiates a DamageTakenByStatus LogLine
     */
    public function initTypeDamageTakenByStatus(Fighter $fighter, $damageTaken, $status): self
    {
        if(!$this->isType(self::TYPE_DAMAGE_TAKEN_BY_STATUS)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'damageTaken' => $damageTaken,
            'statusType' => $status->getStatusType()
        ];

        return $this;
    }

    /**
     * Initiates a HealingTakenByAttack LogLine
     */
    public function initTypeHealingTakenByAttack(Fighter $fighter, int $healAmount): self
    {
        if(!$this->isType(self::TYPE_HEALING_TAKEN_BY_ATTACK)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'healAmount' => $healAmount
        ];

        return $this;
    }

    /**
     * Initiates a KO LogLine
     */
    public function initTypeKO(Fighter $fighter): self
    {
        if(!$this->isType(self::TYPE_KO)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName()
        ];

        return $this;
    }

    /**
     * Initiates a RemainingVitality LogLine 
     */
    public function initTypeRemainingVitality(Fighter $fighter): self
    {
        if(!$this->isType(self::TYPE_REMAINING_VITALITY)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'remainingVitality' => $fighter->getCurrentVitality(),
            'vitality' => $fighter->getVitality()
        ];

        return $this;
    }

    /**
     * Initiates a StatisticModifierStatus Received LogLine
     */
    public function initTypeStatisticModifierStatusReceived(Fighter $fighter, StatisticModifierStatus $statisticModifier): self
    {
        if(!$this->isType(self::TYPE_STATISTIC_MODIFIER_RECEIVED)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'modifierType' => $statisticModifier->getStatusType(), 
            'modifier' => $statisticModifier->getModifier(),
            'statistic' => $statisticModifier->getStatisticModified()
        ];

        return $this;
    }

    /**
     * Initiates a StatisticModifierStatus Loss LogLine
     */
    public function initTypeStatisticModifierStatusLoss(Fighter $fighter, StatisticModifierStatus $statisticModifier): self
    {
        if(!$this->isType(self::TYPE_STATISTIC_MODIFIER_LOSS)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'modifierType' => $statisticModifier->getStatusType(), 
            'modifier' => $statisticModifier->getModifier(),
            'statistic' => $statisticModifier->getStatisticModified()
        ];

        return $this;
    }

    /**
     * Initiates a DamagingStatus Received LogLine
     */
    public function initTypeDamagingStatusReceived(Fighter $fighter, DamagingStatus $damagingStatus): self
    {
        if(!$this->isType(self::TYPE_DAMAGING_STATUS_RECEIVED)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'damagingType' => $damagingStatus->getStatusType()
        ];

        return $this;
    }

    /**
     * Initiates a DamagingStatus Loss LogLine
     */
    public function initTypeDamagingStatusLoss(Fighter $fighter, DamagingStatus $damagingStatus): self
    {
        if(!$this->isType(self::TYPE_DAMAGING_STATUS_LOSS)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'damagingType' => $damagingStatus->getStatusType()
        ];

        return $this;
    }

    /**
     * Initiates a ControlStatus Received LogLine
     */
    public function initTypeControlStatusReceived(Fighter $fighter, ControlStatus $controlStatus): self
    {
        if(!$this->isType(self::TYPE_CONTROL_STATUS_RECEIVED)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'controlType' => $controlStatus->getStatusType()
        ];

        return $this;
    }

    /**
     * Initiates a ControlStatus Loss LogLine
     */
    public function initTypeControlStatusLoss(Fighter $fighter, ControlStatus $controlStatus): self
    {
        if(!$this->isType(self::TYPE_CONTROL_STATUS_LOSS)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'controlType' => $controlStatus->getStatusType()
        ];

        return $this;
    }

    /**
     * Initiates a ControlStatus Activate LogLine
     */
    public function initTypeControlStatusActivate(Fighter $fighter, string $controlStatusType): self
    {
        if(!$this->isType(self::TYPE_CONTROL_STATUS_ACTIVATE)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'controlType' => $controlStatusType
        ];

        return $this;
    }

    /**
     * Initiates a ControlStatus No Effect LogLine
     */
    public function initTypeControlStatusNoEffect(Fighter $fighter, string $controlStatusType): self
    {
        if(!$this->isType(self::TYPE_CONTROL_STATUS_NO_EFFECT)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [
            'fighterName' => $fighter->getName(),
            'controlType' => $controlStatusType
        ];

        return $this;
    }

    /**
     * Initiates a Attack No Effect LogLine
     */
    public function initTypeAttackNoEffect(): self
    {
        if(!$this->isType(self::TYPE_ATTACK_NO_EFFECT)){
            throw new Exception('Wrong type of LogLine used.');
        }

        $this->data = (object) [];

        return $this;
    }
}