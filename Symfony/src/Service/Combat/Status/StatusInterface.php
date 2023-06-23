<?php

namespace App\Service\Combat\Status;

use App\Service\Combat\Fighter;

interface StatusInterface
{
    const TYPE_BUFF = 'buff';
    const TYPE_NERF = 'nerf';
    const TYPE_HEALING = 'healing';

    const TYPE_CONTROL_PETRIFICATION = 'petrification';
    const TYPE_CONTROL_PARALYSIS = 'paralysis';
    const TYPE_CONTROL_CONFUSION = 'confusion';
    const TYPE_CONTROL_FATIGUE = 'fatigue';
    const TYPE_CONTROL_FREEZE = 'freeze';
    const TYPE_CONTROL_SLEEP = 'sleep';
    const TYPE_CONTROL_YAWN = 'yawn';

    const TYPE_DAMAGING_BAD_POISON = 'bad poison';
    const TYPE_DAMAGING_POISON = 'poison';
    const TYPE_DAMAGING_BURN = 'burn';

    /**
     * Returns the remaining number of turns of the status
     */
    public function getRemainingTurns();

    /**
     * Returns the type of the status
     */
    public function getStatusType();

    /**
     * Purge the status
     */
    public function getPurged(Fighter $fighter);

    /**
     * Verifies if the status is still valid, if so reduce the remaining turn by 1.
     */
    public function manageStatus(Fighter $fighter);
}