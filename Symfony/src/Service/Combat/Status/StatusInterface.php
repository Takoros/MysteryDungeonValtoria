<?php

namespace App\Service\Combat\Status;

interface StatusInterface
{
    const TYPE_DAMAGING = 'damaging';
    const TYPE_HEALING = 'healing';
    const TYPE_CONTROL = 'control';
    const TYPE_BUFF = 'buff';
    const TYPE_NERF = 'nerf';

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
    public function getPurged($fighter);
}